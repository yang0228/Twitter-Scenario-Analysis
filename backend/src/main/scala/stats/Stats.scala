/*
 Team 5: LA
 */
package stats

import scala.concurrent.duration._
import com.typesafe.config.ConfigFactory
import akka.actor._
import akka.cluster.Cluster
import akka.cluster.ClusterEvent._
import akka.cluster.MemberStatus
import gnieh.sohva.sync._
import utils.FileParser
import FileParser._
import akka.cluster.ClusterEvent.ReachableMember
import akka.cluster.ClusterEvent.MemberUp
import akka.cluster.ClusterEvent.CurrentClusterState
import akka.cluster.ClusterEvent.UnreachableMember

/*
  1. Go to "application.conf", change hostname to your IP address
  2. Change seed-nodes to all your computing instances' IP address
  3. sbt
  4. runMain stats.StatsComputeNode PORT
    e.g. runMain stats.StatsComputeNode 2551
    The port number is depending on your application.conf.
    In this case, it's 2551.
 */
object StatsComputeNode {
  def main(args: Array[String]): Unit = {
    if (args.isEmpty) {
      startup("2551")
    } else {
      startup(args(0))
    }
  }

  def startup(port: String): Unit = {
    // Override the configuration of the port when specified as program argument
    val config =
      ConfigFactory.parseString(s"akka.remote.netty.tcp.port=" + port).withFallback(
        ConfigFactory.parseString("akka.cluster.roles = [compute]")).
        withFallback(ConfigFactory.load("stats"))

    val system = ActorSystem("ClusterSystem", config)
    system.actorOf(Props[StatsService], name = "statsService")
  }
}

/*
  1. Go to "application.conf", change hostname to your IP address
  2. Change seed-nodes to all your computing instance's IP address
  3. sbt
  4. runMain stats.StatsMergeNode CITY1 CITY2 ...CITYN
    e.g. runMain stats.StatsMergeNode mel la
 */
object StatsMergeNode {
  def main(args: Array[String]): Unit = {
    val cities = if (args.isEmpty) Array("la", "mel") else args
    val system = ActorSystem("ClusterSystem")
    /*
      Read all scenarios which is specified in the scenario.conf
      FYI: Please follow this format:
         CITY_scenario=SCENARIO:GROUP_LEVEL SCENARIO:GROUP_LEVEL ... SCENARIO1:GROUP_LEVEL
         There is an extra blank " " between two scenario.
     */

    val scenarioFile = "/scenario.conf"
    val scenarioList = readConfFile(scenarioFile)
    var scenarios: Map[String, List[Array[String]]] = Map()
    cities.foreach {
      c =>
        var list: List[Array[String]] = List()
        val s = scenarioList.get(c + "_scenario").split(" ").toArray
        s.foreach {
          i =>
            list = i.split(":") :: list
        }
        scenarios += c -> list
    }

    system.actorOf(Props(classOf[StatsMerge], "/user/statsService", cities, scenarios), "merge")
  }
}

class StatsMerge(servicePath: String, cities: Array[String], scenarios: Map[String, List[Array[String]]]) extends Actor {
  val webServerFile = "/webserver.conf"
  val webServerIPList: List[String] = readConfFile(webServerFile).get("webserver").split(" ").toList

  val cluster = Cluster(context.system)
  val servicePathElements = servicePath match {
    case RelativeActorPath(elements) => elements
    case _ => throw new IllegalArgumentException(
      "servicePath [%s] is not a valid relative actor path" format servicePath)
  }

  /*
   Tick the computing instance to do indexing every 5 minutes
   Doing merge job itself every 10 minutes
   */

  import context.dispatcher

  val indexTask = context.system.scheduler.schedule(2.seconds, 5.minutes, self, "index")
  val computeTask = context.system.scheduler.schedule(60.seconds, 10.minutes, self, "compute")

  var nodes = Set.empty[Address]
  var couches: Map[String, CouchClient] = Map()

  override def preStart(): Unit = {
    cluster.subscribe(self, classOf[MemberEvent], classOf[ReachabilityEvent])
  }

  override def postStop(): Unit = {
    cluster.unsubscribe(self)
    computeTask.cancel()
    indexTask.cancel()
  }

  def receive = {
    case "index" if nodes.nonEmpty =>
      nodes.foreach {
        address =>
          val service = context.actorSelection(RootActorPath(address) / servicePathElements)
          service ! MapReduce(address.toString.substring(25).split(":")(0), cities, "get_create_year")
      }

    case "compute" if nodes.nonEmpty =>
      println("Schedule computing task")
      scenarios.keys.foreach {
        city =>
          StatsWorker.pushLatestTweets(couches, city, webServerIPList, 1000)
          scenarios(city).foreach {
            scenario =>
              StatsWorker.mergeResult(couches, city, scenario(0), webServerIPList, scenario(1).toInt)
          }
      }

    /*
    If a instance is leaving the cluster in a safe, expected manner then it switches to the leaving state.
    Once the leader sees the convergence on the instance in the leaving state, the leader will then move it
    to exiting. Once all instances have seen the exiting state (convergence) the leader will remove the node
    from the cluster, marking it as removed.
    If a instance is unreachable then gossip convergence is not possible and therefore any leader actions are
    also not possible (for instance, allowing a instance to become a part of the cluster). To be able to move
    forward the state of the unreachable nodes must be changed. It must become reachable again or marked as down.
    If the instance is to join the cluster again the actor system must be restarted and go through the joining
    process again. The cluster can, through the leader, also auto-down a instance after a configured time of
    unreachability.
     */
    case state: CurrentClusterState =>
      nodes = state.members.collect {
        case m if m.hasRole("compute") && m.status == MemberStatus.Up => m.address
      }
    case MemberUp(m) if m.hasRole("compute") =>
      nodes += m.address
      val addr: String = m.address.toString.substring(25).split(":")(0)
      val couch = new CouchClient(addr, 5984)
      couches += addr -> couch
    case other: MemberEvent =>
      nodes -= other.member.address
    case UnreachableMember(m) =>
      val addr: String = m.address.toString.substring(25).split(":")(0)
      couches -= addr
      nodes -= m.address
    case ReachableMember(m) if m.hasRole("compute") =>
      nodes += m.address
      val addr: String = m.address.toString.substring(25).split(":")(0)
      val couch = new CouchClient(addr, 5984)
      couches += addr -> couch
  }
}

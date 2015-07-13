/*
 Team 5: LA
 */
package harvest

import utils.FileParser
import FileParser._
import twitter4j._
import gnieh.sohva.async._

/*
  1. Please change "slave.conf" first.
    Format: slave=IP1 IP2 IP3...
  2. sbt
  3. runMain harvest.MelStreamer
 */
object MelStreamer {
  def main(args: Array[String]) {
    val couches = scala.collection.mutable.Set[CouchClient]()
    val slaveFile = "/slave.conf"
    val slaveList = readConfFile(slaveFile).get("slave").split(" ").toArray
    slaveList.foreach {
      s =>
        println(s"$s")
        val couch = new CouchClient(s, 5984)
        couches.add(couch)
    }
    val twitterStream = new TwitterStreamFactory(TweetWorker.config).getInstance
    twitterStream.addListener(TweetWorker.statusListener(couches, "mel"))
    val melBox = Array(Array(144.55, -38.22), Array(145.54, -37.54))
    twitterStream.filter(new FilterQuery().locations(melBox))
    Thread.sleep(Long.MaxValue)
    couches.foreach(x => x.shutdown)
    twitterStream.cleanUp
    twitterStream.shutdown

  }
}

/*
  1. Please change "slave.conf" first.
    Format: slave=IP1 IP2 IP3...
  2. sbt
  3. runMain harvest.LAStreamer
 */
object LAStreamer {
  def main(args: Array[String]) {
    val couches = scala.collection.mutable.Set[CouchClient]()
    val slaveFile = "/slave.conf"
    val slaveList = readConfFile(slaveFile).get("slave").split(" ").toArray
    slaveList.foreach {
      s =>
        println(s"$s")
        val couch = new CouchClient(s, 5984)
        couches.add(couch)
    }
    val twitterStream = new TwitterStreamFactory(TweetWorker.config).getInstance
    twitterStream.addListener(TweetWorker.statusListener(couches, "la"))
    val laBox = Array(Array(-118.668167, 33.703732), Array(-118.155212, 34.336781))
    twitterStream.filter(new FilterQuery().locations(laBox))
    Thread.sleep(Long.MaxValue)
    couches.foreach(x => x.shutdown)
    twitterStream.cleanUp
    twitterStream.shutdown
  }
}

/*
  1. Please change "slave.conf" first.
    Format: slave=IP1 IP2 IP3...
  2. Secondly, please change "location.conf" as well.
    Format: CITYNAME=BOUNDINGBOX BOUNDINGBOX BOUNDINGBOX BOUNDINGBOX
    For LA, i.e. la=-118.668167 33.703732 -118.155212 34.336781
  3. sbt
  4. runMain harvest.LocationStreamer CITYNAME
    e.g. runMain harvest.LocationStreamer mel
    please
 */
object LocationStreamer {
  def main(args: Array[String]) {
    val couches = scala.collection.mutable.Set[CouchClient]()
    val slaveFile = "/slave.conf"
    val slaveList = readConfFile(slaveFile).get("slave").split(" ").toArray
    slaveList.foreach {
      s =>
        println(s"$s")
        val couch = new CouchClient(s, 5984)
        couches.add(couch)
    }
    val location = args(0)
    val locationFile = "/location.conf"
    val locationList = readConfFile(locationFile)
    val boundingBoxes = locationList.get(location).split(" ").map(_.toDouble).grouped(2).toArray
    //val  boundingBoxes= args.map(_.toDouble).grouped(2).toArray
    val twitterStream = new TwitterStreamFactory(TweetWorker.config).getInstance
    twitterStream.addListener(TweetWorker.statusListener(couches, args(0)))

    twitterStream.filter(new FilterQuery().locations(boundingBoxes))
    Thread.sleep(Long.MaxValue)
    couches.foreach(x => x.shutdown)
    twitterStream.cleanUp
    twitterStream.shutdown
  }
}


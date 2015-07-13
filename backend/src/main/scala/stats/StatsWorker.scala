/*
 Team 5: LA
 */
package stats

import gnieh.sohva.sync._
import harvest.TweetInfo


case class Result(_id: String, Scenario: String, KeyValue: List[KeyValue], _rev: Option[String] = None)

case class KeyValue(Key: String, Value: Int)

case class LastTweets(_id: String, Scenario: String, Geo: List[LatitudeLongitude],
                      _rev: Option[String] = None)

case class LatitudeLongitude(Latitude: Double, Longitude: Double)

object StatsWorker {

  def mergeResult(couches: Map[String, CouchClient], location: String, scenario: String, targetIpList: List[String],
                  gLevel: Int) {
    var result: Map[List[String], Int] = Map()
    targetIpList.foreach {
      targetIp =>
        val targetCouch = new CouchClient(targetIp, 5984)

        couches.keys.foreach {
          c =>
            val db = couches(c).database(location)
            val design = db.design("analysis", "javascript")
            val view = design.view[List[String], Int, Result](scenario)
            val queryResult = view.query(group_level = gLevel)
            queryResult.foreach {
              i =>
                if (result.contains(i.key)) {
                  val updatedValue = result(i.key) + i.value
                  result += i.key -> updatedValue
                } else {
                  result += i.key -> i.value
                }
            }
        }
        val targetDB = targetCouch.database(location)
        targetDB.create
        var finalResult: List[KeyValue] = List()
        result.keys.foreach {
          i => finalResult = KeyValue(i.mkString(", "), result(i)) :: finalResult
        }
        val targetView = scenario + "_" + gLevel
        targetDB.deleteDoc(targetView)
        targetDB.saveDoc[Result](Result(targetView, targetView, finalResult))
    }
  }

  def pushLatestTweets(couches: Map[String, CouchClient], location: String, targetIpList: List[String], totalSize: Int) {
    val size = totalSize / couches.size
    targetIpList.foreach {
      targetIP =>
        val targetCouch = new CouchClient(targetIP, 5984)
        val targetDB = targetCouch.database(location)
        targetDB.create
        val view = "last_" + totalSize + "_tweets_geo_location"
        targetDB.deleteDoc(view)
        var finalResult: List[LatitudeLongitude] = List()

        couches.keys.foreach {
          c =>
            val db = couches(c).database(location)
            val lastDocsID = db._all_docs(descending = true, skip = 1, limit = size)
            val lastDocs = db.getDocsById[TweetInfo](lastDocsID)
            lastDocs.foreach {
              i =>
                finalResult = LatitudeLongitude(i.latitude.toDouble, i.longitude.toDouble) :: finalResult
            }
        }
        targetDB.saveDoc[LastTweets](LastTweets(view, view, finalResult))
    }
  }
}


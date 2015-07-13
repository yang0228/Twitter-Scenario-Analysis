/*
 Team 5: LA
 */
package stats

import akka.actor.Actor

case class MapReduce(ip: String, cities: Array[String], scenario: String)

class StatsService extends Actor {
  /*
    It's a asynchronous way of doing indexing. Since query the CouchDB, it will return view results before finishing
    indexing. In this case, we don't care the result, although a async call will return the result in the future. We
    utilize this "Future" mechanism to achieve goal.
   */

  import gnieh.sohva.async.CouchClient
  def receive = {
    case MapReduce(ip, cities, scenario) =>
      println("indexing")
      val couch = new CouchClient(ip, 5984)
      cities.foreach {
        city =>
          val db = couch.database(city)
          val design = db.design("analysis", "javascript")
          val view = design.view[List[String], Int, Result](scenario)
          view.query(limit = 1)
      }
  }
}


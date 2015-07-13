/*
 Team 5: LA
 */
package harvest

import utils.FileParser
import FileParser._
import twitter4j._
import gnieh.sohva.async._
import scala.util.Random

object TweetWorker {
  def config: twitter4j.conf.Configuration = {
    val OAuthFile = "/OAuth.conf"
    val OAuthConfig = readConfFile(OAuthFile)
    val OAuthConsumerKey: String = OAuthConfig.get("OAuthConsumerKey")
    val OAuthConsumerSecret: String = OAuthConfig.get("OAuthConsumerSecret")
    val OAuthAccessToken: String = OAuthConfig.get("OAuthAccessToken")
    val OAuthAccessTokenSecret: String = OAuthConfig.get("OAuthAccessTokenSecret")
    val conf = new twitter4j.conf.ConfigurationBuilder()
      .setOAuthConsumerKey(OAuthConsumerKey)
      .setOAuthConsumerSecret(OAuthConsumerSecret)
      .setOAuthAccessToken(OAuthAccessToken)
      .setOAuthAccessTokenSecret(OAuthAccessTokenSecret)
      .setJSONStoreEnabled(true)
      .build
    return conf
  }

  def statusListener(couchs: scala.collection.mutable.Set[CouchClient], location: String) = new StatusListener() {
    val databases = scala.collection.mutable.Set[Database]()

    couchs.foreach {
      i =>
        val database = i.database(location)
        database.create
        databases.add(database)
    }

    override def onStatus(status: Status) = {
      val user: User = status.getUser
      val geoLocation: GeoLocation = status.getGeoLocation
      val place: Place = status.getPlace
      val tweet = TweetInfo(
        status.getId.toString,
        status.getText,
        status.getCreatedAt,
        status.getSource,
        user.getId.toString,
        user.getName,
        user.getScreenName,
        user.getFriendsCount.toString,
        user.getFollowersCount.toString,
        user.getFavouritesCount.toString,
        user.getCreatedAt,
        user.getLang,
        user.getTimeZone,
        user.getLocation,
        place.getFullName,
        geoLocation.getLatitude.toString,
        geoLocation.getLongitude.toString
      )
      /*
        The application uses tweet ID, which is unique, as the documents ID stores on the CouchDB.
        Once the harvester receives tweet, it would try to look up and delete existing documents with
        the same tweet ID on all databases before save them. That is the way of removal of duplicate tweets.
       */
      databases.foreach(i => i.deleteDoc(status.getId.toString))
      val randomDB: Database = databases.toList(Random.nextInt(databases.size))
      println(randomDB.toString)
      randomDB.saveDoc(tweet)
    }

    def onDeletionNotice(statusDeletionNotice: StatusDeletionNotice) {}

    def onTrackLimitationNotice(numberOfLimitedStatuses: Int) {
      println(numberOfLimitedStatuses)
    }

    def onException(ex: Exception) {}

    def onScrubGeo(arg0: Long, arg1: Long) {}

    def onStallWarning(warning: StallWarning) {}
  }
}

case class TweetInfo(_id: String, tweet_text: String, tweet_created_at: java.util.Date,
                     source: String, user_id: String, user_name: String, user_screen_name: String,
                     user_friends: String, user_followers: String, user_favorites: String,
                     user_created_at: java.util.Date, user_language: String, user_timezone: String,
                     user_location: String, place_fullname: String, latitude: String, longitude: String,
                     _rev: Option[String] = None)


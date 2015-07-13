/*
 Team 5: LA
 */
package utils

import scala.Some

object FileParser {

  import scala.io.Source

  def using[A <: {def close() : Unit}, B](resource: A)(f: A => B): B =
    try {
      f(resource)
    } finally {
      resource.close()
    }

  def readConfFile(filename: String): Option[Map[String, String]] = {
    try {
      println(s"loading $filename")
      val lines = using(Source.fromURL(getClass.getResource(filename))) {
        source =>
          (for (line <- source.getLines) yield line).map(_.stripLineEnd.split("=", -1))
            .map(fields => fields(0) -> fields(1)).toList
      }
      val map = Map(lines: _*)
      Some(map)
    } catch {
      case e: Exception => None
    }
  }
}


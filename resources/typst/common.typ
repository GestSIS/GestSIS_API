#let formatDate(date) = if date != none {
  datetime(year:int(date.slice(0,4)), day: int(date.slice(8,10)), month: int(date.slice(5,7)))
    .display("[day].[month].[year repr:last_two]")
}
#let formatTime(date, duree:0) = if date != none { 
  (datetime(hour:int(date.slice(0,2)), minute: int(date.slice(3,5)), second: 0)+duration(minutes:duree))
    .display("[hour]:[minute]")
}

#let parseDateTime(date) = datetime(
  year: int(date.slice(0,4)),
  month: int(date.slice(5,7)),
  day: int(date.slice(8,10)),
  hour: int(date.slice(11,13)),
  minute: int(date.slice(14,16)),
  second: 0)

#let formatDateTime(date) = if date != none {
  parseDateTime(date).display("[day].[month].[year repr:last_two] [hour]:[minute]")
}

#let presenceDuration(presence) = parseDateTime(presence.fin) - parseDateTime(presence.debut)

#let formatDuration(dur) = {
  let totalMinutes = calc.round(dur.seconds() / 60)
  let heures = calc.floor(totalMinutes / 60)
  let minutes = calc.rem(totalMinutes, 60)
  str(heures) + "h" + (if minutes < 10 { "0" + str(minutes) } else { str(minutes) })
}


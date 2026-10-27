#let template(body, portrait: true, background: none) = {
  set text(font: "DM Sans 9pt", weight: "light")

  set page(
    "a4",
    margin: (top: 3cm, left: 2cm, right: 1cm, rest: 2cm),
    header: {
      grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm),
      )
    },
    footer: context {
      let courante = here().page()
      let debuts = query(heading.where(level: 1)).map(h => h.location().page())
      let debut = debuts.filter(p => p <= courante).at(-1, default: 1)
      let fin = debuts.filter(p => p > courante).at(0, default: locate(<fin-document>).page() + 1) - 1
      align(center, [#(courante - debut + 1) / #(fin - debut + 1)])
    },
    flipped: not (portrait),
    background: background,
  )

  show heading.where(level: 1): it => {
    pagebreak(weak: true)
    it
  }

  show table.cell.where(y: 0): strong
  set table(
    fill: (_, y) => if calc.odd(y) { rgb(0, 0, 0, 5%) },
  )

  body
  [#metadata(none) <fin-document>]
}

#let formatDate(date) = if date != none {
  datetime(year: int(date.slice(0, 4)), day: int(date.slice(8, 10)), month: int(date.slice(5, 7))).display(
    "[day].[month].[year repr:last_two]",
  )
}
#let formatTime(date, duree: 0) = if date != none {
  (datetime(hour: int(date.slice(0, 2)), minute: int(date.slice(3, 5)), second: 0) + duration(minutes: duree)).display(
    "[hour]:[minute]",
  )
}

#let parseDateTime(date) = datetime(
  year: int(date.slice(0, 4)),
  month: int(date.slice(5, 7)),
  day: int(date.slice(8, 10)),
  hour: int(date.slice(11, 13)),
  minute: int(date.slice(14, 16)),
  second: 0,
)

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

#let indexBy(ts, by: x => str(x.id)) = {
  ts.fold((:), (acc, el) => {
    let key = by(el)
    let elements = acc.at(key, default: ())
    elements.push(el)
    acc.insert(key, elements)
    acc
  })
}

// Libellé d'une écriture : désignation, suivie du complément s'il est renseigné
// (nature du travail d'une fiche liée, type d'excuse d'une amende)
#let formatService(ecriture) = {
  let complement = ecriture.at("complement", default: none)
  if complement == none or complement == "" [#ecriture.designation] else [#ecriture.designation — #complement]
}

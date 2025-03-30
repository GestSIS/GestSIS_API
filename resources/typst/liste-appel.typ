#set page("a4", margin: (top: 3cm, left: 3cm, right: 2cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
}, numbering: "1")
#set text(font: "DM Sans")

#let formatDate(date) = {
  datetime(year:int(date.slice(0,4)), day: int(date.slice(8,10)), month: int(date.slice(5,7)))
    .display("[day].[month].[year]")
}
#let formatTime(date, duree:0) = {
  (datetime(hour:int(date.slice(0,2)), minute: int(date.slice(3,5)), second: 0)+duration(minutes:duree))
    .display("[hour]:[minute]")
}

#let (exercice, fonctions, excuses) = json("liste-appel.json")


= Liste appel
== #formatDate(exercice.date) #formatTime(exercice.heure) - #exercice.designation

#table(
  columns: (1fr),
  [*Lieu:* #exercice.localite.designation, #exercice.lieu],
  [*Communications:* \ #exercice.communications]
)

#v(0.5cm)
#show table.cell.where(y: 0): strong
#set table(
  fill: (_, y) => if calc.odd(y) { rgb(0, 0, 0, 5%) },
  align: (x, _) => if x > 1 { center } else { left }
)

#table(
  stroke: none,
  columns: (1fr, 1fr, auto, auto, auto, auto),
  table.header(
    [Nom Prénom], [Fonction], [Présent], [Absent], [Remplacé], [Excusé]
  ),
  table.hline(),
  ..exercice.sapeurs.map((presence) => {
    (
      [#presence.display],
      [#fonctions.at(str(presence.fonction_id), default: "")],
      [#if presence.present == 1 [☑] else [☐]],
      [#if presence.absent == 1 [☑] else [☐]],
      [#if presence.remplace == 1 [☑] else [☐]],
      [#if presence.excuse_type_id == 0 [☑] else [☐]],
    )
  }).flatten(),
  table.footer(
    [*Total: #exercice.sapeurs.len()*]
  )
)


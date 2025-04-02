#import "common.typ": formatDate, formatTime
#set text(font: "DM Sans", weight: "extralight")

#set page("a4", margin: (top: 3cm, left: 1.5cm, right: 1cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
}, numbering: "1")

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


#import "common.typ": formatDate, formatTime, template
#show: template

#let (exercice, fonctions, excuses) = json("liste-appel.json")

= Liste appel
== #formatDate(exercice.date) #formatTime(exercice.heure) - #exercice.designation

#table(
  columns: 1fr,
  [*Lieu:* #exercice.localite.designation, #exercice.lieu],
  [*Communications:* \ #exercice.communications]
)

#v(0.5cm)
#set table(
  align: (x, _) => if x > 1 { center } else { left },
)

#table(
  stroke: none,
  columns: (1fr, 1fr, auto, auto, auto, auto),
  table.header([Nom Prénom], [Fonction], [Présent], [Absent], [Remplacé], [Excusé]),
  table.hline(),
  ..exercice
    .sapeurs
    .map(presence => {
      (
        [#presence.display],
        [#fonctions.at(str(presence.fonction_id), default: "")],
        [#if presence.present == 1 [☑] else [☐]],
        [#if presence.absent == 1 [☑] else [☐]],
        [#if presence.remplace == 1 [☑] else [☐]],
        [#if presence.excuse_type_id > 0 [☑] else [☐]],
      )
    })
    .flatten(),
  table.footer([*Total: #exercice.sapeurs.len()*]),
)

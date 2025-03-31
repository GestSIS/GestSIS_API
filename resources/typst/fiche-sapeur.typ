#import "common.typ": formatDate, formatTime
#set text(font: "DM Sans", weight: "extralight")

#set page("a4", margin: (top: 3cm, left: 1.5cm, right: 0.5cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo"), [Centre], [Droite],
    )
}, numbering: "1")

#let data = json("fiche-sapeur.json")

= Titre de la page

Exemple d'utilisation de `typst` avec un fichier JSON.

#show table.cell.where(y: 0): strong

#table(
    columns: (auto, auto, 1fr),
    [Date], [Nb personnes], [Type exercice],
    ..data.exercices.map(ex => {
        ([#ex.date], [#ex.nb-personnes], [#ex.type])
    }).flatten()
)
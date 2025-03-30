#set page("a4", margin: (top: 3cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo"), [Centre], [Droite],
    )
}, numbering: "1")

= Titre de la page

Exemple d'utilisation de `typst` avec un fichier JSON.

#let data = json("fiche-sapeur.json")
#show table.cell.where(y: 0): strong

#table(
    columns: (auto, auto, 1fr),
    [Date], [Nb personnes], [Type exercice],
    ..data.exercices.map(ex => {
        ([#ex.date], [#ex.nb-personnes], [#ex.type])
    }).flatten()
)
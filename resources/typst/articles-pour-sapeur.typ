#import "common.typ": formatDate, formatDateTime, formatTime
#set text(font: "DM Sans", weight: "extralight")

#set page(
  "a4",
  margin: (top: 3cm, left: 2cm, right: 1cm, rest: 2cm),
  header: {
    grid(
      columns: (1fr, 1fr, 1fr),
      align: (left + horizon, center + horizon, right + horizon),
      image("logo", height: 1.5cm),
    )
  },
  numbering: "1 / 1",
)

#let (
  sapeur,
  articles,
) = json("articles-pour-sapeur.json")

#set table(
  fill: (_, y) => if calc.even(y) { rgb(0, 0, 0, 5%) },
)


= Liste articles #emph[#sapeur.prenom #sapeur.nom]

#v(0.3cm)

#table(
  stroke: none,
  columns: (1fr, 1fr, 1fr, 1fr, 1fr),
  table.header([Matériel], [Numéro], [Taille], [Distribué le], [Retour le]),
  table.hline(),
  ..if articles.len() == 0 {
    (table.cell(colspan: 5)[Aucun article],)
  } else {
    articles.map(article => (
      [#article.materiel_type.designation],
      [#article.numero],
      [#article.taille],
      [#formatDate(article.attribution)],
      [],
    ))
  }.flatten(),
)

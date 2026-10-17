#import "common.typ": formatDate, formatDateTime, formatTime, template
#show: template

#let (
  sapeur,
  articles,
) = json("articles-pour-sapeur.json")

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

#import "common.typ": template
#show: template

#let (sapeurs, imageDefault) = json("trombinoscope.json")

= Trombinoscope

#table(
  columns: (1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
  ..sapeurs.map(sapeur => [#figure(
    supplement: none,
    image(if sapeur.photo != none { sapeur.photo } else { imageDefault }),
    caption: [#sapeur.prenom #sapeur.nom],
  )])
)

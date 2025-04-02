#import "common.typ": formatDate, formatTime, formatDateTime
#set text(font: "DM Sans", weight: "extralight")

#set page("a4", margin: (top: 3cm, left: 2cm, right: 1cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
}, numbering: "1")

#let (sapeurs, imageDefault) = json("trombinoscope.json")

#set table(
  fill: (_, y) => if calc.even(y) { rgb(0, 0, 0, 5%) },
)


= Trombinoscope

#table(
  columns:(1fr, 1fr, 1fr, 1fr, 1fr, 1fr),
  ..sapeurs.map(sapeur => 
    [#figure(
      supplement: none,
      image(if sapeur.photo != none { sapeur.photo } else { imageDefault }),
      caption:[#sapeur.prenom #sapeur.nom]
    )]
  )
)

#import "common.typ": formatDate, formatTime
#set text(font: "DM Sans", weight: "extralight")

#set page("a4", margin: (top: 3cm, left: 3cm, right: 2cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
}, numbering: "1 / 1")

#show table.cell.where(y: 0): strong
#set table(
  fill: (_, y) => if calc.odd(y) { rgb(0, 0, 0, 5%) },
)

#let (date, comptes, sapeurs, decomptes) = json("comptes.json")
#let totals = comptes.map(c => c.ecritures.map(e => decimal(e.total)).sum(default: "0.00"))


#if comptes.len() > 1 [= Justificatif comptable]

#for compte in comptes {
  [== #compte.numero #compte.designation]
  v(0.2cm)
  [Etat au #formatDate(date)]
  v(0.2cm)

  table(
    stroke: none,
    columns: (auto, 1fr, auto, auto, auto),
    align: (auto, auto, auto, end, auto),
    table.header(
      [Date], [Libellé], [Sapeur], [Montant], [Payé le]
    ),
    table.hline(),
    ..if compte.ecritures.len() == 0 {
      (
        table.cell(colspan: 3)[Aucune écriture],
        [0.00],
        []
      )
    } else {
      (..compte.ecritures.map(ecriture => (
        [#formatDate(ecriture.date)],
        [#ecriture.designation],
        [#ecriture.sapeur_id],
        [#ecriture.total],
        {if ecriture.decompte_id != none {
          decomptes.at(str(ecriture.decompte_id))
        }},
      )),
      table.hline(),
      table.footer(
        repeat: false,
        table.cell(colspan: 4, align:end)[*#compte.ecritures.map(e => decimal(e.total)).sum()*],
        []
      ))
    }.flatten(),
  )
  
  pagebreak(weak: true)
}

#if comptes.len() > 1 {
  [== Récapitulatif]

  table(
    stroke: none,
    columns: 4,
    align: (auto, auto, end, end),
    table.header([Compte], [], [Nb écritures], [Total]),
    table.hline(),
    ..comptes.zip(totals).map(compte => (
      [#compte.at(0).numero],
      [#compte.at(0).designation],
      [#compte.at(0).ecritures.len()],
      [#compte.at(1)]
    )).flatten(),
    table.hline(),
    table.footer(
      [],
      [],
      [*#comptes.map(c => c.ecritures.len()).sum()*],
      [*#totals.map(total => decimal(total)).sum()*]
    )
  )
}

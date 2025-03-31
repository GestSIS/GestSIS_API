#import "common.typ": formatDate, formatTime
#set text(font: "DM Sans", weight: "extralight")

#set page("a4", margin: (top: 3cm, left: 1.5cm, right: 0.5cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
}, numbering: "1 / 1")

#let (decompte, sapeurs, ecritures, unites) = json("decompte.json")

#let formatTarif(ecriture) = {
  let tarifMin = if ecriture.tarif_min == none [] else [#ecriture.tarif_min CHF/#ecriture.tarif_min_pour h\ puis ];
  let tauxSpecial = if ecriture.taux == none [] else [\* #str(decimal(ecriture.taux) * 100)% \(taux #ecriture.taux_description\)] ;
  return [#tarifMin #ecriture.tarif CHF/#unites.at(str(ecriture.type_unite_id)) #tauxSpecial];
}

#show table.cell.where(y: 0): strong
#set table(
  fill: (_, y) => if calc.odd(y) { rgb(0, 0, 0, 5%) },
)

= Décompte du #formatDate(decompte.date)

#table(
  stroke: none,
  columns: (auto, auto, auto, auto, auto, auto),
  align: (auto, auto, auto, auto, auto, end),
  table.header(
    [Date], [Nature du service], [Sapeur], [Tarif], [Qté], [Total]
  ),
  table.hline(),
  ..ecritures.map((ecriture) => {
    (
      [#if ecriture.date != none { formatDate(ecriture.date) } else [-]],
      [#ecriture.designation],
      [#if ecriture.sapeur_id != none { sapeurs.at(str(ecriture.sapeur_id)) } else [-]],
      [#formatTarif(ecriture)],
      [#ecriture.quantite],
      [#ecriture.total],
    )
  }).flatten()
)
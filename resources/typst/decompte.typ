#set page("a4", margin: (top: 3cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
}, numbering: "1 / 1")
#set text(font: "DM Sans")

#let formatDate(date) = {
  datetime(year:int(date.slice(0,4)), day: int(date.slice(8,10)), month: int(date.slice(5,7)))
    .display("[day].[month].[year repr:last_two]")
}
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
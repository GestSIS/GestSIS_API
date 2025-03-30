#set page("a4", margin: (top: 3cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
}, numbering: "1")
#set text(font: "DM Sans")

#show heading.where(level: 1): it => {
  pagebreak(weak: true)
  counter(page).update(1)
  it
}

#let formatDate(date) = if date != none {
  datetime(year:int(date.slice(0,4)), day: int(date.slice(8,10)), month: int(date.slice(5,7)))
    .display("[day].[month].[year repr:last_two]")
}
#let formatTime(date) = if date != none { 
  datetime(hour:int(date.slice(0,2)), minute: int(date.slice(3,5)), second: 0)
    .display("[hour]:[minute]")
}
#let (decompte, decomptes, sapeurs, ecritures) = json("decompte-par-sapeur.json")

#let formatTarif(ecriture) = {
  let tarifMin = if ecriture.tarif_min == none [] else [#ecriture.tarif_min CHF/#ecriture.tarif_min_pour h puis ];
  let tauxSpecial = if ecriture.taux == none [] else [\* #str(float(ecriture.taux) * 100)% \(taux #ecriture.taux_description\)] ;
  return [#tarifMin #ecriture.tarif CHF/#ecriture.unite #tauxSpecial];
}

#show table.cell.where(y: 0): strong
#set table(
  fill: (_, y) => if calc.odd(y) { rgb(0, 0, 0, 5%) },
)

#let indexBy(ts, by: x => str(x.id)) = {
  ts.fold((:), (acc, el) => {
    let key = by(el)
    let elements = acc.at(key, default: ())
    elements.push(el)
    acc.insert(key, elements)
    acc
  })
}

#for (_, ecrituresParSapeur) in indexBy(ecritures, by: el => str(el.sapeur_id)) {
  let ecriture = ecrituresParSapeur.first()
  
  // Page header
  [= Décompte de frais]
  h(0.5cm)
  table(
    columns: (auto, auto, 1fr),
    fill: rgb(0,0,0,15%),
    stroke: none,
    [#ecriture.civilite], [#ecriture.sapeur], table.cell(
      align: end,
      [Versement sur #ecriture.iban]
    )
  )

  // Page content
  for (_, subEcritures) in indexBy(ecrituresParSapeur, by: el => str(el.ecriture_categorie_id)) {
    [== #subEcritures.first().categorie]

    let ecritureIdentifier = (ecriture) => {
      if ecriture.intervention_id != none {
        "inter"+str(ecriture.intervention_id)
      } else {
        str(ecriture.id)
      }
    }
    table(
      stroke: none,
      columns: (auto, auto, 1fr, auto, auto, auto, auto),
      align: (start, start, start, start, start, start, end),
      table.header(
        [Date], [Heure], [Service], [Tarif], [Qté], [Payé le], [Total]
      ),
      ..indexBy(subEcritures, by: ecritureIdentifier)
        .values()
        .map(ecritures => {
          let ecriture = ecritures.first()
          if ecritures.len() > 10 and ecritures.first().intervention_id != none {
            (
              [#if ecriture.date != none { formatDate(ecriture.date) }],[#formatTime(ecriture.heure)],table.cell(colspan: 5,[#ecriture.designation]),
              ..ecritures.map((ecriture) => {
              (
                table.cell(colspan: 3, []),
                [#formatTarif(ecriture)],
                [#ecriture.quantite],
                [#formatDate(decomptes.at(str(ecriture.decompte_id)).date)],
                [#ecriture.total]
              )
            })).flatten()
          }
          else{
            (if ecriture.date != none { formatDate(ecriture.date) } else [],
              [#formatTime(ecriture.heure)],
              [#ecriture.designation],
              [#formatTarif(ecriture)],
              [#ecriture.quantite],
              [#formatDate(decomptes.at(str(ecriture.decompte_id)).date)],
              [#ecriture.total]).flatten()
          }
        }
      ).flatten()
    )
  }
}

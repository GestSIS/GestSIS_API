#import "common.typ": formatDate, formatService, formatTime, indexBy, template
#show: template

#let (decompte, decomptes, sapeurs, ecritures) = json("decompte-par-sapeur.json")

#let formatTarif(ecriture) = {
  let tarifMin = if ecriture.tarif_min == none [] else [#ecriture.tarif_min CHF/#ecriture.tarif_min_pour h puis ]
  let tauxSpecial = if (
    ecriture.taux == none
  ) [] else [\* #str(decimal(ecriture.taux) * 100)% \(taux #ecriture.taux_description\)]
  return [#tarifMin #ecriture.tarif CHF/#ecriture.unite #tauxSpecial]
}

#for (_, ecrituresParSapeur) in indexBy(ecritures, by: el => str(el.sapeur_id)) {
  let ecriture = ecrituresParSapeur.first()

  // Page header
  [= Décompte de frais]
  h(0.5cm)
  table(
    columns: (auto, auto, 1fr),
    fill: rgb(0, 0, 0, 15%),
    stroke: none,
    [#ecriture.civilite],
    [#ecriture.sapeur],
    table.cell(
      align: end,
      [Versement sur #ecriture.iban],
    ),
  )

  // Page content
  for (_, subEcritures) in indexBy(ecrituresParSapeur, by: el => str(el.ecriture_categorie_id)) {
    [== #subEcritures.first().categorie]

    let ecritureIdentifier = ecriture => {
      if ecriture.intervention_id != none {
        "inter" + str(ecriture.intervention_id)
      } else {
        str(ecriture.id)
      }
    }
    table(
      stroke: none,
      columns: (auto, auto, 1fr, auto, auto, auto, auto),
      align: (start, start, start, start, start, start, end),
      table.header([Date], [Heure], [Service], [Tarif], [Qté], [Payé le], [Total]),
      ..indexBy(subEcritures, by: ecritureIdentifier)
        .values()
        .map(ecritures => {
          let ecriture = ecritures.first()
          if ecritures.len() > 10 and ecritures.first().intervention_id != none {
            (
              [#if ecriture.date != none { formatDate(ecriture.date) }],
              [#formatTime(ecriture.heure)],
              table.cell(colspan: 5, formatService(ecriture)),
              ..ecritures.map(ecriture => {
                (
                  table.cell(colspan: 3, []),
                  [#formatTarif(ecriture)],
                  [#ecriture.quantite],
                  [#formatDate(decomptes.at(str(ecriture.decompte_id)).date)],
                  [#ecriture.total],
                )
              }),
            ).flatten()
          } else {
            (
              if ecriture.date != none { formatDate(ecriture.date) } else [],
              [#formatTime(ecriture.heure)],
              formatService(ecriture),
              [#formatTarif(ecriture)],
              [#ecriture.quantite],
              [#formatDate(decomptes.at(str(ecriture.decompte_id)).date)],
              [#ecriture.total],
            ).flatten()
          }
        })
        .flatten(),
    )
  }
}

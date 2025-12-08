#import "common.typ": formatDate, formatTime
#set text(font: "DM Sans", weight: "extralight")

#set page(
  "a4",
  margin: (top: 3cm, left: 1.5cm, right: 1.5cm, rest: 2cm),
  header: {
    grid(
      columns: (1fr, 1fr, 1fr),
      align: (left + horizon, center + horizon, right + horizon),
      image("logo", height: 1.5cm),
    )
  },
  numbering: "1",
  flipped: true,
)

#show heading.where(level: 1): it => {
  pagebreak(weak: true)
  counter(page).update(1)
  it
}

#let (decompte, decomptes, sapeurs, ecritures, resume) = json("decompte-par-sapeur.json")

#let formatTarif(ecriture) = {
  let tarifMin = if ecriture.tarif_min == none [] else [#ecriture.tarif_min CHF/#ecriture.tarif_min_pour h puis ]
  let tauxSpecial = if (
    ecriture.taux == none
  ) [] else [\* #str(decimal(ecriture.taux) * 100)% \(taux #ecriture.taux_description\)]
  return [#tarifMin #ecriture.tarif CHF/#ecriture.unite #tauxSpecial]
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

#for (sapeurId, ecrituresParSapeur) in indexBy(ecritures, by: el => str(el.sapeur_id)) {
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
      table.hline(),
      ..subEcritures
        .map(ecriture => {
          (
            if ecriture.date != none { formatDate(ecriture.date) },
            formatTime(ecriture.heure),
            ecriture.designation,
            formatTarif(ecriture),
            ecriture.quantite,
            formatDate(decomptes.at(str(ecriture.decompte_id)).date),
            ecriture.total,
          )
        })
        .flatten(),
      table.footer(
        repeat: false,
        table.cell(colspan: 6, align: end, fill: none, [*Sous-total*]),
        table.cell(fill: none, [*#subEcritures.map(e => decimal(e.total)).sum()*]),
      ),
    )
  }

  [=== Résumé]
  let total = ecrituresParSapeur.map(e => decimal(e.total)).sum()
  let paiement = decompte.paiements.find(p => str(p.sapeur_id) == sapeurId)
  let dejaSolde = total - decimal(paiement.total)
  table(
    stroke: none,
    align: (start, end),
    columns: 2,
    [Total], [#total],
    [Déduction AVS/AC], [#paiement.avs_ac],
    [Déjà soldé], [#dejaSolde],
    table.hline(),
    [*Total versé*], [*#paiement.total*],
  )
  [Paiement le: #formatDate(decompte.date)]
}

#if resume [
  = Récapitulatif

  #table(
    columns: (1fr, auto, auto, auto, auto, auto, auto, auto),
    stroke: none,
    align: (start, end, end, end, end, end, end, end),
    table.header(
      [Sapeur], [Solde], [Indemnite], [Cotisations\ AVS/AC], [Frais effectif], [Frais forfaitaire], [Autre], [Total]
    ),
    table.hline(),
    ..decompte
      .paiements
      .map(paiement => {
        (
          sapeurs.at(str(paiement.sapeur_id)),
          paiement.solde,
          paiement.indemnite,
          paiement.avs_ac,
          paiement.frais_effectif,
          paiement.frais_forfaitaire,
          paiement.autre,
          [*#paiement.total*],
        )
      })
      .flatten(),
    // TODO: Optimiser les multiples maps suivantes en ue seule réduction
    table.footer(
      repeat: false,
      ..(
        [Nombre: #decompte.paiements.len()],
        decompte.paiements.map(p => decimal(p.solde)).sum(),
        decompte.paiements.map(p => decimal(p.indemnite)).sum(),
        decompte.paiements.map(p => decimal(p.avs_ac)).sum(),
        decompte.paiements.map(p => decimal(p.frais_effectif)).sum(),
        decompte.paiements.map(p => decimal(p.frais_forfaitaire)).sum(),
        decompte.paiements.map(p => decimal(p.autre)).sum(),
        decompte.paiements.map(p => decimal(p.total)).sum(),
      ).map(el => [*#el*]),
    ),
  )
]

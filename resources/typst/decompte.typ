#import "common.typ": formatDate, formatTime, template
#show: template

#let (decompte, sapeurs, ecritures, unites) = json("decompte.json")

#let formatTarif(ecriture) = {
  let tarifMin = if ecriture.tarif_min == none [] else [#ecriture.tarif_min CHF/#ecriture.tarif_min_pour h\ puis ]
  let tauxSpecial = if (
    ecriture.taux == none
  ) [] else [\* #str(decimal(ecriture.taux) * 100)% \(taux #ecriture.taux_description\)]
  return [#tarifMin #ecriture.tarif CHF/#unites.at(str(ecriture.type_unite_id)) #tauxSpecial]
}

= Décompte du #formatDate(decompte.date)

#table(
  stroke: none,
  columns: (auto, auto, auto, auto, auto, auto),
  align: (auto, auto, auto, auto, auto, end),
  table.header([Date], [Nature du service], [Sapeur], [Tarif], [Qté], [Total]),
  table.hline(),
  ..ecritures
    .map(ecriture => {
      (
        [#if ecriture.date != none { formatDate(ecriture.date) } else [-]],
        [#ecriture.designation],
        [#if ecriture.sapeur_id != none { sapeurs.at(str(ecriture.sapeur_id)) } else [-]],
        [#formatTarif(ecriture)],
        [#ecriture.quantite],
        [#ecriture.total],
      )
    })
    .flatten(),
)

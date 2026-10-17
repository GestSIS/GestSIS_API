#import "common.typ": formatDate, formatTime, template
#show: template

#let (params, sapeurs, exercices, civilites, localites) = json("convocations.json")

#for (id, sapeur) in sapeurs {
  [= #params.titre]

  v(1cm)
  grid(
    columns: (1.2fr, 1fr),
    rows: auto,
    gutter: 3pt,
    [],
    [#civilites.at(str(sapeur.civilite_id)) \
      #sapeur.nom #sapeur.prenom \
      #sapeur.rue #sapeur.no_rue \
      #localites.at(str(sapeur.localite_id)) \ ],
  )
  v(2cm)
  params.texte_debut
  v(0.2cm)

  table(
    stroke: none,
    columns: (auto, auto, 1fr, 1fr, auto),
    table.header([Date], [Heure], [Evenement], [Lieu], []),
    table.hline(),
    ..sapeur
      .exercices
      .map(presence => {
        let exercice = exercices.at(str(presence.exercice_id))
        (
          [#formatDate(exercice.date)],
          [#formatTime(exercice.heure)#if params.affichage_duree [-#formatTime(exercice.heure, duree: exercice.duree)]],
          [#exercice.designation],
          [#localites.at(str(exercice.localite_id)): #exercice.lieu],
          [#if params.affichage_pour_info and presence.convoque != 1 [_Pour information_]],
        )
      })
      .flatten(),
  )
  v(0.2cm)
  params.texte_fin
}

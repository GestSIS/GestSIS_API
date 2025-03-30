#set page("a4", margin: (top: 3cm, left: 3cm, right: 2cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
})
// #set text(font: "DM Sans")

#let formatDate(date) = {
  datetime(year:int(date.slice(0,4)), day: int(date.slice(8,10)), month: int(date.slice(5,7)))
    .display("[day].[month].[year repr:last_two]")
}
#let formatTime(date, duree:0) = {
  (datetime(hour:int(date.slice(0,2)), minute: int(date.slice(3,5)), second: 0)+duration(minutes:duree))
    .display("[hour]:[minute]")
}

#let (params, sapeurs, exercices, civilites, localites) = json("convocations.json")

#for (id, sapeur) in sapeurs {
  [= #params.titre]

  v(1cm)
  grid(
    columns: (1.2fr, 1fr),
    rows: (auto),
    gutter: 3pt,
    [],
    [#civilites.at(str(sapeur.civilite_id)) \
      #sapeur.nom #sapeur.prenom \
      #sapeur.rue #sapeur.nom #sapeur.no_rue \
      #localites.at(str(sapeur.localite_id)) \ ],
  )
  v(2cm)
  params.texte_debut
  v(0.2cm)

  show table.cell.where(y: 0): strong
  set table(
    fill: (_, y) => if calc.odd(y) { rgb(0, 0, 0, 5%) },
  )

  table(
    stroke: none,
    columns: (auto, auto, 2fr, auto, auto),
    table.header(
      [Date], [Heure], [Evenement], [Lieu], []
    ),
    table.hline(),
    ..sapeur.exercices.map((presence) => {
      let exercice = exercices.at(str(presence.exercice_id))
      (
        [#formatDate(exercice.date)],
        [#formatTime(exercice.heure)#if params.affichage_duree [-#formatTime(exercice.heure, duree: exercice.duree)]],
        [#exercice.designation],
        [#localites.at(str(exercice.localite_id)): #exercice.lieu],
        [#if params.affichage_pour_info and presence.convoque != 1 [_Pour information_]]
      )
    }).flatten()
  )
  v(0.2cm)
  params.texte_fin
  pagebreak(weak: true)
}

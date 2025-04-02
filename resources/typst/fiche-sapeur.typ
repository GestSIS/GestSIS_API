#import "common.typ": formatDate, formatTime, formatDateTime
#set text(font: "DM Sans", weight: "extralight")

#set page("a4", margin: (top: 3cm, left: 2cm, right: 1cm, rest: 2cm), header: {
    grid(
        columns: (1fr, 1fr, 1fr),
        align: (left + horizon, center + horizon, right + horizon),
        image("logo", height: 1.5cm)
    )
}, numbering: "1 / 1")

#let (
  sapeur, fonctions, grades, mutations, cours, telephones, permis
) = json("fiche-sapeur.json")

#set table(
  fill: (_, y) => if calc.even(y) { rgb(0, 0, 0, 5%) },
)

= Fiche sapeur

#v(0.3cm)

== Informations personnelles

#table(
  stroke: none,
  columns: (auto, 1fr, auto, 1fr),
  table.header(
    [*Civilité:*],
    table.cell(colspan:3)[#sapeur.civilite.forme_politesse],
    [*Prénom:*],
    [#sapeur.prenom],
    [*Nom:*],
    [#sapeur.nom],
    [*NPA:*],
    [#sapeur.localite.npa],
    [*Localité:*],
    [ #sapeur.localite.designation],
    [*Rue:*],
    [#sapeur.rue],
    [*Numéro:*],
    [#sapeur.no_rue],
    [*N° AVS:*],
    [#sapeur.no_avs],
    [*Cotisation AVS:*],
    if sapeur.cotisation_avs == 1 {
      [☑]
    } else {
      [☐]
    },
    [*Email:*],
    [#sapeur.email],
    [*Date de naissance:*],
    [#formatDate(sapeur.date_naissance)],
    [*Remarque:*],
    table.cell(colspan:3)[#sapeur.remarque],
  )
)

#v(0.3cm)
  
== Informations banquaires

#table(
  stroke: none,
  columns: (auto, 1fr),
  [*IBAN:*],
  [#sapeur.iban],
)

#v(0.3cm)

#show table.cell.where(y: 0): strong

== Numéros de téléphones

#table(
  stroke: none,
  columns: (auto, 1fr, 1fr, 1fr),
  table.header(
    [Priorité], [Numéro], [Type], [Export RTA]
  ),
  table.hline(),
  ..if telephones.len() == 0 {
    (table.cell(colspan: 4)[Aucun numéro],)
  } else {
    telephones.map(telephone => (
      [#telephone.priorite],
      [#telephone.numero],
      [#telephone.telephone_type.type],
      [#if telephone.rta == 1 [☑] else [☐]],
    ))
  }.flatten()
)

== Références professionelles

#table(
  stroke: none,
  columns: (1fr, 1fr, 1fr),
  table.header(
    [Profession], [Employeur], [Lieu de travail]
  ),
  table.hline(),
  [#sapeur.profession], [#sapeur.employeur], [#sapeur.lieu_de_travail]
)

== Fonction principale et grade actuel

#table(
  stroke: none,
  columns: (1fr, 1fr),
  table.header(
    [Fonction principale], [Grade actuel]
  ),
  table.hline(),
  if sapeur.fonction != none [#sapeur.fonction.nom] else [-],
  if sapeur.fonction != none [#sapeur.grade.designation] else [-]
)

== Cours

#table(
  stroke: none,
  columns: (auto, 1fr, 1fr, 1fr),
  table.header(
    [Date], [Désignation], [Lieu], [Durée \[jours\]]
  ),
  table.hline(),
  ..if cours.len() == 0 {
    (table.cell(colspan: 4)[Aucun cours suivi],)
  } else {
    cours.map(cours => (
      [#formatDate(cours.date)],
      [#cours.cours.designation],
      [#cours.localite.designation],
      [#cours.duree],
    ))
  }.flatten()
)

== Fonctions

#table(
  stroke: none,
  columns: (1fr, 1fr, 2fr, 2fr),
  table.header(
    [Début], [Fin], [Fonction], [Remarque]
  ),
  table.hline(),
  ..if fonctions.len() == 0 {
    (table.cell(colspan: 4)[Aucune fonction],)
  } else {
    fonctions.map(fonction => (
      [#formatDate(fonction.debut)],
      [#formatDate(fonction.fin)],
      [#fonction.fonction.nom],
      [#fonction.remarque],
    ))
  }.flatten()
)

== Grades

#table(
  stroke: none,
  columns: (1fr, 1fr, 2fr),
  table.header(
    [Date], [Grade], [Remarque]
  ),
  table.hline(),
  ..if grades.len() == 0 {
    (table.cell(colspan: 4)[Aucun numéro],)
  } else {
    grades.map(grade => (
      [#formatDate(grade.date)],
      [#grade.grade.designation],
      [#grade.remarque],
    ))
  }.flatten()
)

== Mutations

#table(
  stroke: none,
  columns: (1fr, 1fr, 1fr, 2fr),
  table.header(
    [Incorporation], [Sortie], [Motif], [Localité]
  ),
  table.hline(),
  ..if mutations.len() == 0 {
    (table.cell(colspan: 4)[Aucune mutation],)
  } else {
    mutations.map(mutation => (
      [#formatDate(mutation.incorporation)],
      [#formatDate(mutation.sortie)],
      [#mutation.motif],
      [#mutation.localite.designation],
    ))
  }.flatten()
)

== Permis

#table(
  stroke: none,
  columns: 2,
  table.header(
    [Permis], [Date]
  ),
  table.hline(),
  ..if permis.len() == 0 {
    (table.cell(colspan: 2)[Aucun permis],)
  } else {
    permis.map(permi => (
      [#permi.permis_type.type],
      [#formatDate(permi.date)],
    ))
  }.flatten()
)

// == Matériel

// TODO: future

#import "common.typ": formatDate, formatDateTime, formatTime
#set text(font: "DM Sans", weight: "extralight")

#set page(
  "a4",
  margin: (top: 3cm, left: 1.5cm, right: 1cm, rest: 2cm),
  header: {
    grid(
      columns: (1fr, 1fr, 1fr),
      align: (left + horizon, center + horizon, right + horizon),
      image("logo", height: 1.5cm),
    )
  },
  numbering: "1",
)

#let (
  intervention,
  params,
  vehicules,
  materiels,
  groupes,
  sapeurs,
  quittances,
  presences,
  ecritures,
) = json("rapport-intervention.json")

#set table(
  fill: (_, y) => if calc.even(y) { rgb(0, 0, 0, 5%) },
)


= Rapport d'intervention

#v(0.3cm)

#table(
  stroke: none,
  columns: (auto, 1fr, auto, 1fr),
  table.header(
    [*Début:*],
    [#formatDate(intervention.date_debut) #formatTime(intervention.heure_debut)],
    [*Fin:*],
    [#formatDate(intervention.date_fin) #formatTime(intervention.heure_fin)],
    [*Lieu*],
    table.cell(colspan: 3, [#intervention.localite.npa #intervention.localite.designation, #intervention.lieu]),
  ),
)


#if params.infoGeneral {
  v(0.3cm)
  [== Informations générales]

  table(
    stroke: none,
    columns: (auto, 1fr, auto, 1fr),
    [*Type d'intervention*],
    [#intervention.type_intervention.designation],
    [*Chef d'intervention*],
    [#intervention.chef_intervention.nom #intervention.chef_intervention.prenom],

    [*Objet*],
    table.cell(
      colspan: 3,
      [#intervention.objet],
    ),

    [*Statistique fédérale*],
    [#intervention.stat_federal.designation],
    if params.statut [*Traitement*],
    if params.statut [#intervention.traitement.designation],

    [*Personnes Sauvées*],
    [#intervention.sauve_personne],
    [*Animaux sauvés*],
    [#intervention.sauve_animaux],

    [*Propriétaire*],
    [#intervention.proprietaire],
    [*Responsable*],
    [#intervention.responsable],

    [*Rapport de police*],
    { if intervention.rapport_police [☑ #intervention.agent] else [☐] },
    [*GPS (wgs84)*],
    [#intervention.wgs84],
    ..if params.description {
      (
        table.cell(colspan: 4, [*Description :*]),
        table.cell(colspan: 4, [#intervention.description]),
      )
    },
  )
}


#if params.vehicules {
  v(0.3cm)
  [== Véhicules mobilisés]

  table(
    stroke: none,
    columns: 1fr,
    ..if intervention.vehicules.len() > 0 {
      intervention.vehicules.map(v => [#vehicules.at(str(v.vehicule_id))])
    } else { ([Aucun véhicule engagé],) }
  )
}

#if params.materiel {
  v(0.3cm)
  [== Matériel consommable ou en prêt]

  table(
    stroke: none,
    columns: 1fr,
    ..if intervention.materiels.len() > 0 {
      intervention.materiels.map(m => [#materiels.at(str(m.materiel_id))])
    } else { ([Aucun matériel],) }
  )
}

#if params.missions {
  v(0.3cm)
  [== Missions]

  table(
    stroke: none,
    columns: (auto, auto, auto, auto, auto),
    ..if intervention.missions.len() > 0 {
      (
        table.header(
          [*Début*],
          [*Quittance*],
          [*Titre*],
          [*Résumé*],
          [*Responsable*],
        ),
        intervention.missions.map(mission => (
          [#mission.debut],
          [#mission.fin],
          [#mission.titre],
          [#mission.resume],
          {
            if mission.sapeur_id != none {
              let sapeur = mission.sapeur_object
              [#sapeur.nom #sapeur.prenom]
            } else [#mission.sapeur]
          },
        )),
      ).flatten()
    } else { ([Aucune mission],) }
  )
}


#if params.appels {
  v(0.3cm)
  [== Partenaires contactés]

  table(
    stroke: none,
    columns: (auto, auto, auto, 1fr),
    ..if intervention.appels.len() > 0 {
      (
        table.header(
          [*Date*],
          [*Nom*],
          [*Numéro*],
          [*Commentaire*],
        ),
        intervention.appels.map(appel => (
          [#appel.date],
          [#appel.nom],
          [#appel.numero],
          [#appel.commentaire],
        )),
      ).flatten()
    } else { ([Aucun appel],) }
  )
}

#if params.jalons {
  v(0.3cm)
  [== Jalons]

  table(
    stroke: none,
    columns: (auto, auto, 1fr),
    ..if intervention.jalons.len() > 0 {
      (
        table.header(
          [*Date*],
          [*Titre*],
          [*Description*],
        ),
        intervention.jalons.map(jalon => (
          [#jalon.date_time],
          [#jalon.titre],
          [#jalon.description],
        )),
      ).flatten()
    } else { ([Aucun jalon],) }
  )
}

#if params.groupes {
  v(0.3cm)
  [== Groupes alarmés]

  table(
    stroke: none,
    columns: 1fr,
    ..if intervention.groupes.len() > 0 {
      intervention.groupes.map(groupe => [#groupe.no #groupe.designation])
    } else { ([Aucun groupe d'alarme],) }
  )
}


#if params.presences or params.presencesResume {
  v(0.3cm)
  [== Présences]

  show table.cell.where(y: 0): strong

  if params.presences {
    table(
      stroke: none,
      columns: (auto, auto, 1fr, auto),
      align: (auto, center, auto, end),
      table.header([Sapeur], [Quittance], [Presence], if params.montants [Montant]),
      ..if intervention.presences.len() > 0 {
        (
          ..presences
            .values()
            .map(sapeur => (
              [#sapeur.nom #sapeur.prenom],
              { if quittances.len() > 0 and quittances.at(str(sapeur.id), default: none) != none [☑] else [☐] },
              [#formatDateTime(sapeur.presences.first().debut) - #formatDateTime(sapeur.presences.first().fin)],
              if params.montants and ecritures.at(str(sapeur.id), default: none) != none {
                let ecriture = ecritures.at(str(sapeur.id))
                [#ecriture]
              } else [],
              ..if sapeur.presences.len() > 1 {
                sapeur
                  .presences
                  .slice(1)
                  .map(presence => (
                    table.cell(colspan: 2, []),
                    table.cell(colspan: 2, [#formatDateTime(presence.debut) - #formatDateTime(presence.fin)]),
                  ))
                  .flatten()
              },
            )),
          if params.montants {
            table.footer(table.cell(colspan: 3)[], [*#ecritures.at("total")*])
          },
        )
          .filter(el => el != none)
          .flatten()
      } else { ([Aucune présence],) },
    )
  } else if params.presencesResume {
    let resumeParSapeurs = intervention.presences.fold((:), (acc, el) => {
      let key = str(el.sapeur_id)
      let elements = acc.at(key, default: ())
      elements.push(el)
      acc.insert(key, elements)
      acc
    })
    [
      Quittances: #quittances.len()

      Nombre sapeurs: #resumeParSapeurs.len()
    ]
  }
}

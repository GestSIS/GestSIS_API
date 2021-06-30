<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <style>
        .page-break {
            page-break-after: always;
        }

        .column-right {
            text-align: right !important;
            padding-right: 1rem !important;
        }

        .sum-row {
            background-color: white !important;
        }
    </style>

    <title>Rapport d'intervention</title>
</head>
<body>
<div>
  <div class="container-fluid">
    <div class="row bg-secondary text-light">
      <div class="col-8"><h1>Informations générales</h1></div>
      <div class="col-4"></div>
    </div>
    
    <div class="row">
        <div class="col-6">
            <h5 for="m-int-date-debut">Date</h5> : 
            <span class="">29.12.2020 12:00</span>
        </div>
        <div class="col-3">
          <div class="form-group">
            <label for="m-int-date-fin">Fin</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <div class="input-group-text">
                  <font-awesome-icon :icon="['far', 'calendar-alt']" />
                </div>
              </div>
              <input
                class="form-control"
                :class="{ 'is-invalid': errors['date_fin'] }"
                :min="dateFinMin"
                :max="dateFinMax"
                type="date"
                id="m-int-date-fin"
                name="date_fin"
                v-model="activeInterventionData.date_fin"
              />
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="form-group">
            <label for="m-int-heure_fin">Heure</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <div class="input-group-text">
                  <font-awesome-icon :icon="['far', 'clock']" />
                </div>
              </div>
              <input
                type="time"
                class="form-control"
                :class="{
                  'is-invalid': errors['heure_fin']
                }"
                id="m-int-heure_fin"
                name="heure_fin"
                v-model="activeInterventionData.heure_fin"
              />
            </div>
          </div>
        </div>
      </div>
      <!-- OBJET -->
      <div class="form-group">
        <label for="m-int-objet">Objet</label>
        <input
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors['objet'] }"
          id="m-int-objet"
          name="objet"
          v-model="activeInterventionData.objet"
        />
      </div>

      <!-- LIEU -->
      <div class="form-group">
        <label for="m-int-lieu">Lieu (Rue, N°)</label>
        <input
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors['lieu'] }"
          id="m-int-lieu"
          name="lieu"
          v-model="activeInterventionData.lieu"
        />
      </div>
      <!-- NPA + LOCALITE -->
      <div class="form-group">
        <label for="m-int-localite">Localité</label>
        <select
          class="custom-select required"
          :class="{ 'is-invalid': errors['localite_id'] }"
          id="m-int-localite"
          name="localite_id"
          style="width: 100%"
          v-model="activeInterventionData.localite_id"
        >
          <option
            v-for="localite in listLocalitesSis"
            :key="localite.id"
            :value="localite.id"
            >Saulcy
          </option>
        </select>
      </div>
      <!-- Chef d'intervention -->
      <div class="form-group">
        <label for="m-int-sapeur">Chef d'intervention</label>
        <select
          class="custom-select required"
          :class="{ 'is-invalid': errors['sapeur_id'] }"
          id="m-int-sapeur"
          name="localite_id"
          style="width: 100%"
          v-model="activeInterventionData.sapeur_id"
        >
          <option
            v-for="sapeur in listSapeur"
            :key="sapeur.id"
            :value="sapeur.id"
            >Georges
          </option>
        </select>
      </div>
    </div>
    {{-- <div class="row">
      <div class="col-2">{{$exercice->date}}</div>
      <div class="col-2">{{$exercice->heure}}</div>
      <div class="col-8">{{$exercice->designation}}</div>
    </div>
    <div class="row">
      <div class="col-12">{{$exercice->communications}}</div>
    </div> --}}
    <table class="table table-sm table-striped">
    </table>
  </div>
</div>
</body>
</html>

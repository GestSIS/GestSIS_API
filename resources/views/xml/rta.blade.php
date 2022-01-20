<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<?php
use Carbon\Carbon;
?>
<Transfert>
	<SIS>{{ $sis }}</SIS>
	<DateMutation>{{ Carbon::parse($date)->format('d.m.Y') }}</DateMutation>
	<Info>{{ $communication }}</Info>
  <Sapeurs>
    @foreach ($sapeurs as $sapeur)
    <Sapeur>
      <Mutation>{{ $sapeur['type'] }}</Mutation>
      <Nom>{{ $sapeur['nom'] }}</Nom>
      <Prenom>{{ $sapeur['prenom'] }}</Prenom>
      <Suffixe>{{ $sapeur['suffixe'] }}</Suffixe>
      <TelNatel>{{ count($sapeur['numeros']) > 0 ? $sapeur['numeros'][0] : '' }}</TelNatel>
      <TelPriv>{{ count($sapeur['numeros']) > 1 ? $sapeur['numeros'][1] : '' }}</TelPriv>
      <TelProf>{{ count($sapeur['numeros']) > 2 ? $sapeur['numeros'][2] : ''}}</TelProf>
      <Fonction>{{ $sapeur['fonction'] }}</Fonction>
      <Date>{{ Carbon::parse($sapeur['date_naissance'])->format('d.m.Y') }}</Date>
      <Adresse>{{ $sapeur['adresse'] }}</Adresse>
      <Localite>{{ $sapeur['localite'] }}</Localite>
      <Groupes>
        @foreach ($sapeur['groupes'] as $groupe)
        <Groupe No="{{ $groupe['no'] }}">{{ $groupe['designation'] }}</Groupe>
        @endforeach
      </Groupes>
    </Sapeur>
    @endforeach
  </Sapeurs>
</Transfert>
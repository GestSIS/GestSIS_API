<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<Transfert>
	<SIS>{{ $sis }}</SIS>
	<DateMutation>{{ $date }}</DateMutation>
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
      <Date>{{ $sapeur['date_naissance'] }}</Date>
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
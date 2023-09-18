<div>

  <table class="table table-sm table-secondary mt-3">
    <tr>
      <th colspan="2">{{ $compte->numero }} {{ $compte->designation }}</th>
      <td>Etat au {{ date('d.m.y') }}</td>
    </tr>
  </table>
  <table class="table table-sm">
    <thead>
      <tr>
        <th>Date</th>
        <th>Libellé</th>
        <th>Sapeur</th>
        <th>Montant</th>
        <th>Payé le</th>
      </tr>
    </thead>
    <tbody>
      <?
      $total = 0;
      ?>
      @if (count($compte->ecritures) <= 0)
        <tr>
          <td colspan="5">Aucune écriture</td>
        </tr>
      @endif
      @foreach ($compte->ecritures as $ecriture)
        <?
        $total += $ecriture->total;
        ?>
        <tr>
          <td>{{ implode('.', array_reverse(explode('-', $ecriture->date))) }}</td>
          <td>{{ $ecriture->designation }}</td>
          <td>{{ $ecriture->sapeur_id ? $sapeurs[$ecriture->sapeur_id] : '-' }}</td>
          <td>{{ number_format($ecriture->total, 2, '.', "'") }}</td>
          <td>
            {{ $ecriture->decompte_id ? implode('.', array_reverse(explode('-', $decomptes[$ecriture->decompte_id]))) : '' }}
          </td>
        </tr>
      @endforeach
    </tbody>
    <tbody>
      <tr>
        <th colspan="3"></th>
        <th>{{ number_format($total, 2, '.', "'") }}</th>
        <th></th>
      </tr>
    </tbody>
  </table>
</div>

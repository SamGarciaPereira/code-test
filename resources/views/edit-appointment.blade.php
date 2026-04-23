@extends('layouts.main')
@section('title', 'Rusky Vet - A saúde do seu cão em primeiro lugar')
@section('content')
  <section class="py-6 border-bottom">
    <div class="container text-center">
      <h1>Consulta #{{ $appointment->id }}</h1>

      <div class="row mt-4 justify-content-center">
        <div class="col-md-10 text-left">

          <div class="text-center mb-4">
            <!-- exibe a foto do paciente ou fallback padrão -->
            <img src="{{ optional($appointment->patient)->photo ? asset('storage/' . $appointment->patient->photo) : asset('images/dog.jpg') }}" class="radius" height="140" alt="Foto de {{ optional($appointment->patient)->name ?? 'paciente' }}">
          </div>

          <table class="table">
            <tbody>
              <tr>
                <th>Consulta</th>
                <td>{{ $appointment->id }}</td>
              </tr>
              <tr>
                <th>Status</th>
                <td>{{ $appointment->is_finished ? 'FINALIZADA' : 'AGENDADA' }}</td>
              </tr>
              <tr>
                <th>Data e hora</th>
                <td>{{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($appointment->time)->format('H:i') }}</td>
              </tr>
              <tr>
                <th>Nome do paciente</th>
                <td>{{ optional($appointment->patient)->name ?? '-' }}</td>
              </tr>
              <tr>
                <th>Raça</th>
                <td>{{ optional($appointment->patient)->breed ?? '-' }}</td>
              </tr>
              <tr>
                <th>Idade</th>
                <td>{{ optional($appointment->patient)->birthdate ? $appointment->patient->getAge() : '-' }}</td>
              </tr>
              <tr>
                <th>Dono</th>
                <td>{{ optional(optional($appointment->patient)->user)->name ?? '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row mt-6 justify-content-center">
        <div class="col-md-6 text-left">
          <form action="{{ route('vet.edit-appointment.post', $appointment->id) }}" method="POST">
            @csrf
            <div class="form-group">
              <!-- observações obrigatórias para finalizar a consulta -->
              <label for="observations">Observações</label>
              <textarea name="observations" rows="7" class="form-control @error('observations') is-invalid @enderror" id="observations">{{ old('observations', $appointment->observations) }}</textarea>
              @error('observations')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-4" {{ $appointment->is_finished ? 'disabled' : '' }}>Salvar e finalizar consulta</button>
          </form>
        </div>
      </div>
    </div>
  </section>
@endsection

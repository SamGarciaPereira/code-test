@extends('layouts.main')
@section('title', 'Rusky Vet - A saúde do seu cão em primeiro lugar')
@section('content')
  <section class="py-6 border-bottom">
    <div class="container text-center">
      <h1>Olá {{ explode(' ', trim(auth()->User()->name))[0] }}!</h1>

      <div class="row mt-6 justify-content-center">
        <div class="col-md-3">
          <p>
            <img src="{{ asset('images/dog.jpg') }}" class="round" width="100">
          </p>
          <p class="lead mt-4">Cadastrar cachorro</p>
          <!-- vet responsável pela consulta -->
          <p>
            <a class="btn btn-primary" href="{{ route('client.edit-patient') }}" role="button">Cadastrar</a>
          </p>
        </div>
        <div class="col-md-3">
          <p>
            <img src="{{ asset('images/appointment.jpg') }}" class="round" width="100">
          </p>
          <p class="lead mt-4">Agendar consulta</p>
          <p>
            <a class="btn btn-primary" href="{{ route('client.create-appointment') }}" role="button">Agendar</a>
          </p>
          <!-- mostra quem fechou a consulta, quando houver -->
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 border-bottom">
    <div class="container text-center">
      <h3>Consultas agendadas</h3>
      <div class="row mt-5 justify-content-center">
        <div class="col-12 col-lg-10">
          <!-- mostra todas as consultas com dados do dono e do cachorro -->
          @if (isset($appointments) && $appointments->count() > 0)
            <table class="table" style="width: 100%">
              <thead>
                <tr>
                  <th>Status</th>
                  <th>Nome do dono</th>
                  <th>Nome do cachorro</th>
                  <th>Data da consulta</th>
                  <th>Horário</th>
                  <th>Veterinário</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($appointments as $appointment)
                  <tr>
                    <td>{{ $appointment->is_finished ? 'FINALIZADA' : 'AGENDADA' }}</td>
                    <td>{{ optional(optional($appointment->patient)->user)->name ?? '-' }}</td>
                    <td>{{ optional($appointment->patient)->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->time)->format('H:i') }}</td>
                    <td>{{ optional($appointment->vet)->name ?? '-' }}</td>
                    <td>
                      <a href="{{ route('vet.edit-appointment', $appointment->id) }}">Abrir</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            Nenhuma consulta agendada até o momento.
          @endif

        </div>
      </div>
    </div>
  </section>

  <section class="py-5 border-bottom">
    <div class="container text-center">
      <h3>Cachorros cadastrados</h3>
      <div class="row mt-5 justify-content-center">
        <div class="col-12 col-lg-10">
          <!-- lista todos os cachorros com dados do dono -->
          @if (isset($patients) && $patients->count() > 0)
            <table class="table" style="width: 100%">
              <thead>
                <tr>
                  <th>Foto</th>
                  <th>Nome do dono</th>
                  <th>Nome do cachorro</th>
                  <th>Raça</th>
                  <th>Data de nascimento</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($patients as $patient)
                  <tr>
                    <td>
                      <img src="{{ $patient->photo ? asset('storage/' . $patient->photo) : asset('images/dog.jpg') }}" alt="Foto de {{ $patient->name }}" class="radius" width="40">
                    </td>
                    <td>{{ optional($patient->user)->name ?? '-' }}</td>
                    <td>{{ $patient->name }}</td>
                    <td>{{ $patient->breed }}</td>
                    <td>{{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d/m/Y') : '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            Nenhum cachorro cadastrado até o momento.
          @endif
        </div>
      </div>
    </div>
  </section>
@endsection

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
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 border-bottom">
    <div class="container text-center">
      <h3>Minhas consultas</h3>
      <div class="row mt-5 justify-content-center">
        <div class="col-12 col-lg-10">
          <!-- exibe consultas reais do cliente autenticado -->
          @if (isset($appointments) && $appointments->count() > 0)
            <table class="table" style="width: 100%">
              <thead>
                <tr>
                  <th>Status</th>
                  <th>Nome do cachorro</th>
                  <th>Data da consulta</th>
                  <th>Horário</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($appointments as $appointment)
                  <tr>
                    <td>{{ $appointment->is_finished ? 'FINALIZADA' : 'AGENDADA' }}</td>
                    <td>{{ optional($appointment->patient)->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->time)->format('H:i') }}</td>
                    <td>
                      <a href="{{ route('client.view-appointment', $appointment->id) }}">Abrir</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            Você ainda não possui consultas agendadas.
          @endif
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 border-bottom">
    <div class="container text-center">
      <h3>Meus cachorros</h3>
      <div class="row mt-5 justify-content-center">
        <div class="col-12 col-lg-10">
          <!-- lista apenas cachorros com cadastro completo -->
          @if (auth()->User()->Patient()->count() === 0)
            Você não tem nenhum cachorr cadastrado.
          @else
            <table class="table" style="width: 100%">
              <thead>
                <tr>
                  <th>Foto</th>
                  <th>Nome</th>
                  <th>Idade</th>
                  <th>Data de nascimento</th>
                  <th>Raça</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach (auth()->User()->Patient()->where('name', '!=', null)->get() as $patient)
<tr>
										<td> <!-- mostra a foto do cachorro, ou uma foto padrão caso ele não tenha -->
											<img src="{{ $patient->photo ? asset('storage/' . $patient->photo) : asset('images/dog.jpg') }}" class="radius" width="40" alt="Foto de {{ $patient->name }}">
										</td>
										<td>{{ $patient->name }}</td>
										<td>{{ $patient->getAge() }}</td>
										<td>{{ $patient->birthdate->format('d/m/Y') }}</td>
										<td>{{ $patient->breed }}</td>
										<td>
											<a href="{{ route('client.edit-patient', $patient->id) }}" class="mx-2" title="Editar">✏️</a>
											<a href="javascript:if (confirm('Você tem certeza que deseja remover este cachorro?'))
                  location.href='{{ route('client.remove-patient', $patient->id) }}'" class="mx-2" title="Remover">❌</a>
                  </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>
    </div>
  </section>
@endsection

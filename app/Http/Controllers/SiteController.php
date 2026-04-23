<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Patient;
use App\Models\User;

use Carbon\Carbon;

class SiteController extends Controller {

    public function getIndex(Request $request) {
		return view('index');
	}

	// ------------------ Cliente ------------------
	public function getClient(Request $request) {
		// vet vai direto para o painel próprio
		if (auth()->user()->type === 'VET') {
			return redirect()->route('vet');
		}

		$user = auth()->user();
		// filtra apenas os cachorros do usuário logado
		$patientIds = Patient::where('user_id', $user->id)->pluck('id');
		// lista apenas consultas dos cachorros desse cliente
		$appointments = \App\Models\Appointment::whereIn('patient_id', $patientIds)
												->orderBy('date')->orderBy('time')->get();

		return view('client', ['appointments' => $appointments]);
  }

	public function getEditPatient($patient_id = null) {
		$user = auth()->User();
		if (!$patient_id) {
			// vet cria novo paciente e escolhe o dono no formulário
			if ($user->type === 'VET') {
				$patient = new Patient();
				$owners = User::where('type', 'CLIENT')->orderBy('name')->get();
				return view('edit-patient', [ 'patient' => $patient, 'owners' => $owners ]);
			}

			$patient = Patient::where([ 'user_id' => $user->id, 'name' => null ])->first();

			if (!$patient) {
				$patient = Patient::create([ 'user_id' => $user->id ]);
			}

			return redirect()->route('client.edit-patient', $patient->id);
		}
		else {
			// busca o paciente 
			$patient = Patient::findOrFail($patient_id);
			
			// verifica a policy antes de mostrar o form
			$this->authorize('update', $patient);
		}

		$owners = [];
		if ($user->type === 'VET') {
			$owners = User::where('type', 'CLIENT')->orderBy('name')->get();
		}

		return view('edit-patient', [ 'patient' => $patient, 'owners' => $owners ]);
	}

	public function postEditPatient(Request $request, $patient_id = null) {
		$user = auth()->user();
		$patient = $patient_id ? Patient::findOrFail($patient_id) : new Patient();

		// se não houver ID, o veterinário está criando um paciente novo
		if (!$patient_id) {
			$patient = new Patient();
			
			// valida e atribui o dono escolhido pelo veterinário no select
			$request->validate(['user_id' => 'required|exists:users,id']);
			$patient->user_id = $request->user_id;

		} else {
			// se houver ID, busca no banco e valida permissão 
			$patient = Patient::findOrFail($patient_id);
			$this->authorize('update', $patient);
			
			// permite que o veterinário troque o dono de um cachorro já existente
			if ($user->type === 'VET' && $request->has('user_id')) {
				$request->validate(['user_id' => 'required|exists:users,id']);
				$patient->user_id = $request->user_id;
			}
		}

		// validação dos dados obrigatórios do cachorro
		$request->validate([
			'name'      => 'required|string|max:255',
			'breed'     => 'required|string|max:255',
			'gender'    => 'required|in:M,F',
			'birthdate' => 'required|date_format:d/m/Y',
			'photo'     => 'nullable|image|max:2048'
		]);

		$data = array_merge($request->except(['birthdate', 'photo', 'user_id']), [ 
			'birthdate' => Carbon::createFromFormat('d/m/Y', $request->birthdate)->format('Y-m-d') 
		]);

		if ($request->hasFile('photo')) {
			$data['photo'] = $request->file('photo')->store('patients', 'public');
		}

		// preenche os dados e salva
		$patient->fill($data);
		$patient->save();

		return redirect()->route('client')->with('toast', 'Paciente salvo com sucesso.');
	}

	public function getRemovePatient($patient_id) {
		
		// busca o paciente, ou falha se ele não existir
		$patient = Patient::findOrFail($patient_id);

		// consulta a nova policy para verificar se o usuário tem permissão de editar esse paciente
		$this->authorize('delete', $patient);

		$patient->delete();

		return redirect()->route('client')->with('toast', 'Paciente removido com sucesso.');
	}

	public function getAppointment($appointment_id) {
		$user = auth()->user();
		$patientIds = Patient::where('user_id', $user->id)->pluck('id');

		$appointment = \App\Models\Appointment::with(['patient', 'vet'])
			->whereIn('patient_id', $patientIds)->findOrFail($appointment_id);
			
		return view('appointment', [ 'appointment' => $appointment ]);
	}

	public function getCreateAppointment() {
		$user = auth()->user();

		// vet agenda para qualquer cachorro já cadastrado
		if ($user->type === 'VET') {
			$patients = Patient::with('user')->whereNotNull('name')->orderBy('name')->get();
		} else {
			$patients = Patient::where('user_id', $user->id)->whereNotNull('name')->orderBy('name')->get();
		}

		return view('create-appointment', [ 'patients' => $patients ]);
	}

	public function postCreateAppointment(Request $request) {
		// valida os dados enviados pelo form de agendamento
		$request->validate([
			'patient' => 'required|exists:patients,id',
			'date'    => 'required|date_format:d/m/Y',
			'time'    => 'required|date_format:H:i'
		]);

		$patient = Patient::findOrFail($request->patient);
		// garante que o cliente só agenda para o próprio cachorro
		$this->authorize('update', $patient); 

		// impede agendamento para paciente sem cadastro completo
		if (!$patient->name) {
			return back()->withErrors(['patient' => 'cadastre o cachorro antes de agendar uma consulta.'])->withInput();
		}

		// impede agendamento em datas passadas
		$appointmentDate = Carbon::createFromFormat('d/m/Y', $request->date)->startOfDay();
		if ($appointmentDate->lt(Carbon::today())) {
			return back()->withErrors(['date' => 'a consulta não pode ser agendada no passado.'])->withInput();
		}

		$date = $appointmentDate->format('Y-m-d');

		// impede dupla reserva no mesmo dia e horário
		$isBooked = \App\Models\Appointment::where('date', $date)->where('time', $request->time)->exists();
		if($isBooked) return back()->withErrors(['time' => 'Este horário acabou de ser reservado.'])->withInput();

		\App\Models\Appointment::create([
			'patient_id' => $patient->id,
			'date'       => $date,
			'time'       => $request->time,
		]);

		return redirect()->route(auth()->user()->type === 'VET' ? 'vet' : 'client')->with('toast', 'Consulta marcada com sucesso.');
	}

	public function postEditAppointment($appointment_id, Request $request) {
		$appointment = \App\Models\Appointment::findOrFail($appointment_id);
		
		// bloqueia nova edição após finalização
		if($appointment->is_finished) {
			return back()->with('toast', 'Esta consulta já foi finalizada e não pode ser alterada.');
		}

		// observações são obrigatórias para finalizar
		$request->validate(['observations' => 'required|string']);

		$appointment->update([
			'observations' => $request->observations,
			'vet_id'       => auth()->id(), 
			'is_finished'  => true     
		]);

		return redirect()->route('vet')->with('toast', 'Consulta finalizada com sucesso.');
	}

	// ------------------ Veterinário ------------------
	public function getVet(Request $request) {
		// carrega as consultas do sistema para o topo do painel
		$appointments = \App\Models\Appointment::with(['patient.user'])
			->orderBy('date', 'asc')
			->orderBy('time', 'asc')
			->get();

		// carrega todos os cachorros cadastrados com o dono vinculado
		$patients = Patient::with('user')
			->whereNotNull('name')
			->orderBy('name', 'asc')
			->get();

		return view('vet', [ 'appointments' => $appointments, 'patients' => $patients ]);
	}

	public function getEditAppointment($appointment_id) {
		// carrega todos os dados exibidos na tela de edição
		$appointment = \App\Models\Appointment::with(['patient.user', 'vet'])
			->findOrFail($appointment_id);
		return view('edit-appointment', [ 'appointment' => $appointment ]);
	}

	public function getAvailableTimes(Request $request) {
		// sem data selecionada, não há horários para retornar
		if (!$request->date) return response()->json([]);
		$dateObject = Carbon::createFromFormat('d/m/Y', $request->date)->startOfDay();

		// não retorna horários para datas passadas
		if ($dateObject->lt(Carbon::today())) {
			return response()->json([]);
		}

		$date = $dateObject->format('Y-m-d');
		
    // extrai apenas a hora e minuto das consultas já marcadas
		$bookedTimes = \App\Models\Appointment::where('date', $date)->pluck('time')
            ->map(fn($t) => Carbon::parse($t)->format('H:i'))->toArray();

    // 1 consulta por hora, horário comercial
		$businessHours = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
		$available = array_values(array_diff($businessHours, $bookedTimes));
		
		return response()->json($available);
	}
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Patient;

use Carbon\Carbon;

class SiteController extends Controller {

    public function getIndex(Request $request) {
		return view('index');
	}

	// ------------------ Cliente ------------------
	public function getClient(Request $request) {
		return view('client');
	}

	public function getEditPatient($patient_id = null) {
		$user = auth()->User();
		if (!$patient_id) {
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

		return view('edit-patient', [ 'patient' => $patient ]);
	}

	public function postEditPatient($patient_id, Request $request) {
		// busca o paciente, ou falha se ele não existir
		$patient = Patient::findOrFail($patient_id);
 
		// consulta a nova policy para verificar se o usuário tem permissão de editar esse paciente
		$this->authorize('update', $patient);


		// validação dos campos + obrigatoriedade
		$request->validate([
			'name'      => 'required|string|max:255',
			'breed'     => 'required|string|max:255',
			'gender'    => 'required|in:M,F',
			'birthdate' => 'required|date_format:d/m/Y',
			'photo'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048' //upload de imagem aceitando apenas arquivos jpeg, jpg e png, com tamanho 		máximo de 2MB
		]);

		$data = array_merge($request->except('birthdate', 'photo'), [ 
			'birthdate' => Carbon::createFromFormat('d/m/Y', $request->birthdate)->format('Y-m-d') 
		]);

    // upload de arquivo armazenado publicamente
		if ($request->hasFile('photo')) {
			$data['photo'] = $request->file('photo')->store('patients', 'public');
		}
		

		$patient->update( $data );

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
		// - TODO: Retornar consulta
		$appointment = null;
		return view('appointment', [ 'appointment' => $appointment ]);
	}

	public function getCreateAppointment() {
		return view('create-appointment');
	}

	public function postCreateAppointment(Request $request) {
		// - TODO: Agendar a consulta
		return redirect()->route('client')->with('toast', 'Consulta marcada com sucesso.');
	}

	// ------------------ Veterinário ------------------
	public function getVet(Request $request) {
		// - TODO: Retornar todos os agendamentos
		$appointments = [];
		return view('vet', [ 'appointments' => $appointments ]);
	}

	public function getEditAppointment($appointment_id) {
		// - TODO: Retornar consulta
		$appointment = null;
		return view('edit-appointment', [ 'appointment' => $appointment ]);
	}

}

@foreach([
    'patient' => 'Patient',
    'agent_accueil' => 'Accueil',
    'personnel_medical' => 'Personnel médical',
    'responsable' => 'Responsable',
    'admin' => 'Administrateur',
] as $role => $label)
    <option value="{{ $role }}" @selected($selectedRole === $role)>{{ $label }}</option>
@endforeach

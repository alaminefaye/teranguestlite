<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\StaffSection;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class StaffController extends Controller
{
    /** Départements proposés pour l'affectation du personnel. */
    public static function departmentOptions(): array
    {
        return [
            'Réception',
            'Service en chambre',
            'Restaurant',
            'Bar',
            'Cuisine',
            'Ménage',
            'Spa',
            'Sécurité',
            'Maintenance',
            'Comptabilité',
            'Ressources humaines',
            'Autre',
        ];
    }

    private function getEnterpriseId(): int
    {
        $id = auth()->user()->enterprise_id;
        if (!$id) {
            abort(403, 'Accès réservé à un administrateur d\'hôtel.');
        }
        return $id;
    }

    /** Retourne le staff membre si il appartient à l'entreprise et est bien role=staff. */
    private function findStaff(int $id): User
    {
        $user = User::where('enterprise_id', $this->getEnterpriseId())
            ->where('role', 'staff')
            ->findOrFail($id);
        return $user;
    }

    /**
     * Liste du personnel (staff) de l'hôtel.
     */
    public function index(Request $request): View
    {
        $enterpriseId = $this->getEnterpriseId();

        $query = User::where('enterprise_id', $enterpriseId)
            ->where('role', 'staff')
            ->orderBy('department')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('department', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $staff = $query->paginate(15);

        $departments = User::where('enterprise_id', $enterpriseId)
            ->where('role', 'staff')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->sort()
            ->values();

        $total = User::where('enterprise_id', $enterpriseId)->where('role', 'staff')->count();

        return view('pages.dashboard.staff.index', [
            'title' => 'Staff',
            'staff' => $staff,
            'departments' => $departments,
            'departmentOptions' => self::departmentOptions(),
            'total' => $total,
        ]);
    }

    public function create(): View
    {
        $this->getEnterpriseId();
        return view('pages.dashboard.staff.create', [
            'title' => 'Ajouter un membre du staff',
            'departmentOptions' => self::departmentOptions(),
            'sectionOptions' => StaffSection::labels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $enterpriseId = $this->getEnterpriseId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'department' => ['nullable', 'string', 'max:100', Rule::in(self::departmentOptions())],
            'managed_sections' => ['nullable', 'array'],
            'managed_sections.*' => ['string', Rule::in(StaffSection::all())],
        ]);

        $validated['enterprise_id'] = $enterpriseId;
        $validated['role'] = 'staff';
        $validated['password'] = Hash::make($validated['password']);
        $validated['department'] = $validated['department'] ?? null;
        $validated['managed_sections'] = $validated['managed_sections'] ?? [];

        User::create($validated);

        return redirect()->route('dashboard.staff.index')
            ->with('success', 'Membre du staff ajouté avec succès.');
    }

    public function edit(int $id): View
    {
        $staff = $this->findStaff($id);
        return view('pages.dashboard.staff.edit', [
            'title' => 'Modifier ' . $staff->name,
            'staff' => $staff,
            'departmentOptions' => self::departmentOptions(),
            'sectionOptions' => StaffSection::labels(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $staff = $this->findStaff($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $staff->id,
            'department' => ['nullable', 'string', 'max:100', Rule::in(self::departmentOptions())],
            'managed_sections' => ['nullable', 'array'],
            'managed_sections.*' => ['string', Rule::in(StaffSection::all())],
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['string', 'confirmed', Password::min(8)];
        }
        $validated = $request->validate($rules);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->department = $validated['department'] ?? null;
        $staff->managed_sections = $validated['managed_sections'] ?? [];
        if (!empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }
        $staff->save();

        return redirect()->route('dashboard.staff.index')
            ->with('success', 'Membre du staff mis à jour avec succès.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $staff = $this->findStaff($id);
        $name = $staff->name;
        $staff->delete();
        return redirect()->route('dashboard.staff.index')
            ->with('success', 'Membre du staff « ' . $name . ' » supprimé.');
    }
}

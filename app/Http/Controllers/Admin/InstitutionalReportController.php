<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstitutionalReportController extends Controller
{
    public function index(Request $request): View
    {
        $month = $this->validatedMonth($request);
        $report = $this->reportData($month);

        return view('admin.reports.index', compact('month', 'report'));
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $this->validatedMonth($request);
        $report = $this->reportData($month);
        $rows = [
            ['Reporte institucional del Centro de Salud San Juan', $month],
            ['Pacientes registrados en el mes', $report['patient_count']],
            ['Citas registradas en el mes', $report['appointment_count']],
            ['Citas pendientes', $report['appointment_counts']['pending']],
            ['Citas confirmadas', $report['appointment_counts']['confirmed']],
            ['Citas atendidas', $report['appointment_counts']['completed']],
            ['Citas canceladas', $report['appointment_counts']['cancelled']],
            [],
            ['Médico', 'Citas asignadas', 'Consultas completadas', 'Atención (%)'],
        ];

        foreach ($report['doctors'] as $doctor) {
            $rows[] = [
                $this->safeCsvValue($doctor->user->name),
                $doctor->appointments_count,
                $doctor->completed_appointments_count,
                $doctor->attendance_rate,
            ];
        }

        return response()->streamDownload(function () use ($rows): void {
            $stream = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($stream, $row, ',', '"', '\\');
            }

            fclose($stream);
        }, 'reporte-institucional-'.$month.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedMonth(Request $request): string
    {
        $data = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        return $data['month'] ?? now()->format('Y-m');
    }

    /**
     * @return array{
     *     patient_count: int,
     *     appointment_count: int,
     *     appointment_counts: array{pending: int, confirmed: int, completed: int, cancelled: int},
     *     doctors: Collection<int, StaffProfile>
     * }
     */
    private function reportData(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $period = [$start->toDateString(), $end->toDateString()];
        $appointmentQuery = Appointment::query()->whereBetween('appointment_date', $period);

        $patientCount = User::query()
            ->whereHas('role', fn (Builder $query) => $query->where('code', 'patient'))
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $appointmentCounts = (clone $appointmentQuery)
            ->select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $doctors = StaffProfile::query()
            ->whereHas('user.role', fn (Builder $query) => $query->where('code', 'doctor'))
            ->with('user')
            ->withCount([
                'appointments' => fn (Builder $query) => $query->whereBetween('appointment_date', $period),
                'appointments as completed_appointments_count' => fn (Builder $query) => $query
                    ->whereBetween('appointment_date', $period)
                    ->where('status', 'completed'),
            ])
            ->orderBy('employee_number')
            ->get();

        $doctors->each(function (StaffProfile $doctor): void {
            $doctor->setAttribute(
                'attendance_rate',
                $doctor->appointments_count === 0
                    ? 0
                    : round($doctor->completed_appointments_count / $doctor->appointments_count * 100, 1),
            );
        });

        return [
            'patient_count' => $patientCount,
            'appointment_count' => (clone $appointmentQuery)->count(),
            'appointment_counts' => [
                'pending' => (int) $appointmentCounts->get('pending', 0),
                'confirmed' => (int) $appointmentCounts->get('confirmed', 0),
                'completed' => (int) $appointmentCounts->get('completed', 0),
                'cancelled' => (int) $appointmentCounts->get('cancelled', 0),
            ],
            'doctors' => $doctors,
        ];
    }

    private function safeCsvValue(string $value): string
    {
        return preg_match('/^[\t\r ]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }
}

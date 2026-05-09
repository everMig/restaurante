<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Reservation::class);

        // Traemos reservas futuras y las de hoy (incluso si pasaron hace unas horas)
        $reservations = Reservation::where('reservation_time', '>=', Carbon::now()->startOfDay()) 
            ->orderBy('reservation_time', 'asc')
            ->with('table')
            ->get();
            
        $tables = Table::where('status', 'available')->get();

        return view('reservations.index', compact('reservations', 'tables'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Reservation::class);

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'reservation_time' => 'required|date|after_or_equal:now',
            'people' => 'required|integer|min:1',
            'table_id' => 'nullable|exists:tables,id',
            'note' => 'nullable|string|max:1000',
        ]);

        Reservation::create($validated);

        return redirect()->back()->with('success', 'Reserva agendada correctamente.');
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $request->validate(['status' => 'required|in:confirmed,cancelled']);
        $reservation->update(['status' => $request->status]);
        
        $msg = $request->status == 'confirmed' ? 'Reserva confirmada.' : 'Reserva cancelada.';
        return redirect()->back()->with('success', $msg);
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);

        $reservation->delete();
        return redirect()->back()->with('success', 'Reserva eliminada.');
    }
}
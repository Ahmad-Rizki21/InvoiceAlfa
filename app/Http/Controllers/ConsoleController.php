<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketTimer;
use App\Models\User;
use App\Services\Websocket\Ws;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ConsoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('quasar');
    }

    public function dataTotalTicketOpen(Request $request)
    {
        $now = Carbon::now();

        $entry = Ticket::where('status', Ticket::STATUS_OPEN)
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);

        if (! $this->isSuperAdmin()) {
            $entry->where('created_by_id', $request->user()->id);
        }

        $entry = $entry->count();

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'total' => $entry,
            ],
        ]);
    }

    public function dataTotalTicket(Request $request)
    {
        $now = Carbon::now();

        if ($this->isSuperAdmin()) {
            $entry = Ticket::whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year)
                        ->count();
        } else {
            $entry = Ticket::where('created_by_id', $request->user()->id)
                            ->whereMonth('created_at', $now->month)
                            ->whereYear('created_at', $now->year)
                            ->count();
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'total' => $entry,
            ],
        ]);
    }

    public function dataAverageWorkingClock(Request $request)
    {
        if ($this->isSuperAdmin()) {
            $entry = Cache::remember(static::class . 'dataAverageWorkingClock', 10 * 60, function () {
                return TicketTimer::calculateAverageTimer([TicketTimer::TIMER_OPEN, TicketTimer::TIMER_START], null, Carbon::now());
            });
        } else {
            $userId = $request->user()->id;

            $entry = Cache::remember(static::class . 'dataAverageWorkingClock'.$userId, 10 * 60, function () use ($userId) {
                return TicketTimer::calculateAverageTimer([TicketTimer::TIMER_OPEN, TicketTimer::TIMER_START], $userId, Carbon::now());
            });
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'total' => $entry,
            ],
        ]);
    }

    public function dataMedianWorkingClock(Request $request)
    {

        if ($this->isSuperAdmin()) {
            $entry = Cache::remember(static::class . 'dataMedianWorkingClock', 15 * 60, function () {
                return TicketTimer::calculateMedianTimer([TicketTimer::TIMER_OPEN, TicketTimer::TIMER_START], null, Carbon::now());
            });
        } else {
            $userId = $request->user()->id;

            $entry = Cache::remember(static::class . 'dataMedianWorkingClock'.$userId, 15 * 60, function () use ($userId) {
                return TicketTimer::calculateMedianTimer([TicketTimer::TIMER_OPEN, TicketTimer::TIMER_START], $userId, Carbon::now());
            });
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'total' => $entry,
            ],
        ]);
    }

    public function dataTotalUsers()
    {
        $entry = Cache::remember(static::class . 'dataTotalUsers', 10 * 60, function () {
            return User::count();
        });

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'total' => $entry,
            ],
        ]);
    }

    public function chartTicketTimer(Request $request)
    {
        $ticketTimer = [];
        $now = Carbon::now()->subDays(7);

        foreach (range(1, 7) as $i) {
            $today = $now->copy()->addDay($i);
            $key = $today->format('Y-m-d');

            if (! isset($ticketTimer[$key])) {
                $ticketTimer[$key] = [
                    'working_clock' => 0,
                    'pending_clock' => 0,
                ];
            }

            $ticketTimer[$key]['working_clock'] += (float) TicketTimer::calculateTotalTimer(null, $today, [TicketTimer::TIMER_OPEN, TicketTimer::TIMER_START]);
            $ticketTimer[$key]['pending_clock'] += (float) TicketTimer::calculateTotalTimer(null, $today, TicketTimer::TIMER_PENDING);
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'chart' => $ticketTimer,
            ],
        ]);
    }

    public function chartTotalTicket(Request $request)
    {
        $total = [];
        $now = Carbon::now()->subDays(7);

        foreach (range(0, 6) as $i) {
            $today = $now->copy()->addDay($i);
            $key = $today->format('Y-m-d');

            if (! isset($total[$key])) {
                $total[$key] = 0;
            }

            $total[$key] += (int) Ticket::whereDate('created_at', $today)->count();
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'chart' => $total,
            ],
        ]);
    }

    public function generateStorageLink()
    {
        if (file_exists(public_path('storage'))) {
            return $this->error('The "public/storage" directory already exists.');
        }

        $publicPath = public_path('storage');

        if (file_exists($publicPath)) {
            if (is_link($publicPath)) {
                unlink($publicPath);
            }
        }

        try {
            app()->make('files')->link(
                storage_path('app/public'), $publicPath
            );
        } catch (\Throwable $e) {
            dd($e);
            //
        }

        abort(404);
    }

    public function testDownload()
    {
        return response()->download(storage_path('testfile'));
    }

    public function debugWs(Ws $ws)
    {
        return $ws->debugServer();
    }
}

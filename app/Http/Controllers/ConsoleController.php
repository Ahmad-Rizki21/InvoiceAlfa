<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\TicketTimer;
use App\Models\User;
use App\Services\Websocket\Ws;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

    public function dataTotalUnpaidAmount(Request $request)
    {
        $now = Carbon::now();

        $unpaid = Invoice::where('status', InvoiceStatus::Unpaid->value)
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year)
                        ->sum('total');

        $total = Invoice::where('status', '!=', InvoiceStatus::Draft->value)
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year)
                        ->sum('total');

        $unpaid = $total - $unpaid;

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'unpaid' => $unpaid,
                'total' => $total,
            ],
        ]);
    }

    public function dataTotalUnpaidCustomer(Request $request)
    {
        $type = $request->type;
        $now = Carbon::now();

        $unpaid = [];
        $unpaidEntries = Invoice::where('status', InvoiceStatus::Unpaid->value)
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);

        if ($type === 'dc') {
            $unpaidEntries->whereNull('franchise_id');
        } else if ($type === 'fr') {
            $unpaidEntries->whereNull('distribution_center_id');
        }

        $unpaidEntries = $unpaidEntries->get(['distribution_center_id', 'franchise_id']);


        foreach ($unpaidEntries as $entry) {
            $customer = $entry->distribution_center_id ?? $entry->franchise_id;

            if (! in_array($customer, $unpaid)) {
                $unpaid[] = $customer;
            }
        }

        $total = [];
        $totalEntries = Invoice::where('status', '!=', InvoiceStatus::Draft->value)
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);

        if ($type === 'dc') {
            $totalEntries->whereNull('franchise_id');
        } else if ($type === 'fr') {
            $totalEntries->whereNull('distribution_center_id');
        }

        $totalEntries = $totalEntries->get(['distribution_center_id', 'franchise_id']);

        foreach ($totalEntries as $entry) {
            $customer = $entry->distribution_center_id ?? $entry->franchise_id;

            if (! in_array($customer, $total)) {
                $total[] = $customer;
            }
        }

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'unpaid' => count(array_unique($unpaid)),
                'total' => count(array_unique($total)),
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

    public function dataTotalPendingReview()
    {
        $now = Carbon::now();

        $total = Invoice::where('status', InvoiceStatus::PendingReview->value)
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year)
                        ->count(['id']);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'total' => $total,
            ],
        ]);
    }

    public function dataTotalRejected()
    {
        $now = Carbon::now();

        $total = Invoice::where('status', InvoiceStatus::Rejected->value)
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year)
                        ->count(['id']);

        return $this->json([
            'status' => 'success',
            'message' => __('Ok'),
            'data' => [
                'total' => $total,
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

    public function dataBackupProgram(Request $request)
    {
        if ((isset($_SERVER['PHP_AUTH_USER']) && ($_SERVER['PHP_AUTH_USER'] == "invoiceprinter")) and
            (isset($_SERVER['PHP_AUTH_PW']) && ($_SERVER['PHP_AUTH_PW'] == "backup"))
        ) {
        } else {
            return response('', 401, [
                'WWW-Authenticate' => 'Basic realm=\"InvoicePrinter\"',
            ]);
        }

        Artisan::call('db:backup');
        $output = Artisan::output();

        preg_match_all('~stored to (.+\.sql)~', $output, $matches);

        $tmpPaths = [];

        $results = '';

        if (isset($matches, $matches[1])) {
            foreach ($matches[1] as $match) {
                if (strpos($match, sys_get_temp_dir()) !== false) {
                    $tmpPaths[] = $match;
                }

                $content = @file_get_contents($match);

                if ($content) {
                    $results .= $content;
                    $results .= "\n\n\n\n\n\n\n\n\n";
                }
            }
        }

        foreach ($tmpPaths as $tmpPath) {
            @unlink($tmpPath);
        }

        return response($results, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="invoice_printer.sql"',
            'Content-Length' => strlen($results),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
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

<?php

namespace App\Http\Controllers;

use App\Models\GmailConnection;
use App\Models\GmailImport;
use App\Services\GmailImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class GmailController extends Controller
{
    public function __construct(private readonly GmailImportService $gmail)
    {
    }

    public function index(): View
    {
        return view('gmail.index', [
            'connection' => GmailConnection::primary(),
            'configured' => $this->gmail->configured(),
            'imports' => GmailImport::with('jobPost')->latest()->paginate(10),
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('gmail_oauth_state', $state);

        try {
            return redirect()->away($this->gmail->authorizationUrl($state));
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('gmail.index')
                ->with('status', $exception->getMessage());
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        $sessionState = (string) $request->session()->pull('gmail_oauth_state');

        abort_unless(
            $sessionState !== '' && hash_equals($sessionState, (string) $request->query('state')),
            403,
        );

        $request->validate(['code' => ['required', 'string']]);

        try {
            $connection = $this->gmail->exchangeCode($request->string('code')->toString());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('gmail.index')
                ->with('status', 'Gmail 連携に失敗しました。Google Cloud の設定を確認してください。');
        }

        return redirect()
            ->route('gmail.index')
            ->with('status', "{$connection->email} と連携しました。");
    }

    public function import(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:500'],
            'limit' => ['required', 'integer', 'min:1', 'max:25'],
        ]);

        $connection = GmailConnection::primary();

        if (! $connection) {
            return redirect()
                ->route('gmail.index')
                ->with('status', '先に Gmail アカウントを接続してください。');
        }

        try {
            $count = $this->gmail->importRecent($connection, $data['query'], (int) $data['limit']);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('gmail.index')
                ->with('status', 'Gmail からの取り込みに失敗しました。認証状態と検索条件を確認してください。');
        }

        return redirect()
            ->route('gmail.index')
            ->with('status', "{$count}件のメールを求人として取り込みました。");
    }

    public function demoImport(): RedirectResponse
    {
        $count = $this->gmail->createDemoImports();

        return redirect()
            ->route('gmail.index')
            ->with('status', "デモ求人を{$count}件取り込みました。");
    }
}

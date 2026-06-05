<?php

namespace App\Http\Controllers;

use App\Models\GmailConnection;
use App\Models\GmailImport;
use App\Models\GmailOauthSetting;
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

    public function index(Request $request): View
    {
        return view('gmail.index', [
            'connection' => GmailConnection::primary($request->user()->id),
            'configured' => $this->gmail->configured(),
            'oauthSetting' => GmailOauthSetting::current(),
            'imports' => GmailImport::with('jobPost')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(10),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        abort_if(app()->isProduction(), 404);

        $data = $request->validate([
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:2000'],
            'redirect_uri' => ['required', 'url', 'max:2048'],
        ]);

        $setting = GmailOauthSetting::current();
        $setting->fill([
            'client_id' => $data['client_id'],
            'redirect_uri' => $data['redirect_uri'],
        ]);

        if ($request->filled('client_secret')) {
            $setting->client_secret = $data['client_secret'];
        }

        $setting->save();

        return redirect()
            ->route('gmail.index')
            ->with('status', 'Google OAuth 設定を保存しました。続けて「Gmail を接続」を押してください。');
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

        if ($sessionState === '' || ! hash_equals($sessionState, (string) $request->query('state'))) {
            return redirect()
                ->route('gmail.index')
                ->with('status', 'Gmail 連携は、Gmail 連携画面の「Gmail を接続」から開始してください。');
        }

        if (! $request->filled('code')) {
            return redirect()
                ->route('gmail.index')
                ->with('status', 'Google 認証コードを取得できませんでした。もう一度「Gmail を接続」から試してください。');
        }

        try {
            $connection = $this->gmail->exchangeCode(
                $request->string('code')->toString(),
                $request->user()->id,
            );
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

        $connection = GmailConnection::primary($request->user()->id);

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

    public function demoImport(Request $request): RedirectResponse
    {
        $count = $this->gmail->createDemoImports($request->user()->id);

        return redirect()
            ->route('gmail.index')
            ->with('status', "デモ求人を{$count}件取り込みました。");
    }
}

@extends('layouts.app')

@section('content')
    @php($mode = 'signin')

    <style>
        .auth-stage { min-height:100vh; display:grid; place-items:center; padding:2rem; overflow:hidden; background:radial-gradient(circle at 8% 10%,#fff0db,transparent 28%),radial-gradient(circle at 92% 88%,#dfe8ff,transparent 28%),#f4f3ef; font-family:'Avenir Next','Segoe UI',sans-serif }
        .auth-shell { position:relative; width:min(1160px,100%); min-height:720px; overflow:hidden; border:1px solid #e5ddd5; border-radius:34px; background:#fffdf9; box-shadow:0 34px 85px -35px #0f172a66 }
        .auth-brand,.auth-panel { position:absolute; inset-block:0; width:50%; transition:transform .72s cubic-bezier(.77,0,.18,1),border-radius .72s cubic-bezier(.77,0,.18,1) }
        .auth-brand { left:0; z-index:3; display:flex; flex-direction:column; overflow:hidden; padding:44px; color:white; background:linear-gradient(145deg,#1e293b,#0f172a 48%,#334155); border-radius:0 120px 120px 0 }
        .auth-shell[data-mode=signup] .auth-brand { transform:translateX(100%); border-radius:120px 0 0 120px }
        .auth-panel { left:50%; display:grid; place-items:center; padding:42px 60px; background:#fffdf9 }
        .auth-shell[data-mode=signup] .auth-panel { transform:translateX(-100%) }
        .auth-form { position:absolute; width:min(440px,calc(100% - 64px)); transition:opacity .3s,transform .5s,visibility .3s }
        .auth-form[data-form=signup] { opacity:0; visibility:hidden; transform:translateX(45px) }
        .auth-shell[data-mode=signup] [data-form=signin] { opacity:0; visibility:hidden; transform:translateX(-45px) }
        .auth-shell[data-mode=signup] [data-form=signup] { opacity:1; visibility:visible; transform:none; transition-delay:.25s }
        .brand-mark { position:relative; z-index:1; display:flex; align-items:center; gap:12px; font-weight:900; letter-spacing:.08em }
        .brand-icon { display:grid; place-items:center; width:42px; height:42px; border:1px solid #ffffff44; border-radius:14px; background:#ffffff1f }
        .brand-copy { position:relative; z-index:1; margin:auto; text-align:center }
        .brand-copy h2 { font-size:44px; font-weight:950 }
        .brand-copy p { margin-top:18px; color:#ffffffc2; line-height:1.7 }
        .brand-switch { position:relative; z-index:1; margin:0 auto 48px; text-align:center; color:#ffffffb8 }
        .brand-switch button { display:block; min-width:200px; margin:15px auto 0; border:1px solid #ffffffaa; border-radius:999px; padding:12px; color:white; font-weight:850; transition:.2s }
        .brand-switch button:hover { background:white; color:#172033; transform:translateY(-2px) }
        .orb { position:absolute; border-radius:50%; background:#ffffff12; border:1px solid #ffffff16 }
        .orb-one { width:260px; height:260px; right:-90px; top:-70px } .orb-two { width:180px; height:180px; left:-70px; bottom:-65px }
        .eyebrow { color:#b45309; font-size:12px; font-weight:900; letter-spacing:.18em; text-transform:uppercase }
        .auth-form h1 { margin-top:8px; color:#111827; font-size:38px; font-weight:950 }
        .intro { margin-top:8px; color:#6b7280; font-size:14px }
        .fields { display:grid; gap:14px; margin-top:26px }
        .field label { display:block; margin-bottom:6px; color:#374151; font-size:13px; font-weight:800 }
        .input-wrap { position:relative }
        .field input:not([type=checkbox]) { width:100%; height:52px; border:1px solid #d9cbbf; border-radius:14px; padding:0 48px 0 15px; outline:none }
        .field input:focus { border-color:#b45309; box-shadow:0 0 0 4px #b453091f }
        .password-toggle { position:absolute; right:10px; top:50%; transform:translateY(-50%); padding:7px; color:#8c725f; font-size:12px; font-weight:800 }
        .remember { display:flex; gap:8px; color:#5f6571; font-size:13px }
        .error { margin-top:4px; color:#dc2626; font-size:12px }
        .alert { margin-top:16px; border:1px solid #fecaca; border-radius:12px; padding:10px; color:#b91c1c; background:#fff1f2; font-size:13px }
        .submit { width:100%; margin-top:20px; border-radius:999px; padding:14px; color:white; background:linear-gradient(90deg,#1e293b,#475569); box-shadow:0 15px 28px -13px #0f172aaa; font-weight:900; transition:.2s }
        .submit:hover { transform:translateY(-2px) }
        .mobile-switch { display:none; margin-top:16px; text-align:center; font-size:13px }.mobile-switch button { color:#b45309; font-weight:900 }
        @media(max-width:800px){.auth-stage{padding:14px}.auth-shell{min-height:760px}.auth-brand{display:none}.auth-panel,.auth-shell[data-mode=signup] .auth-panel{left:0;width:100%;padding:25px;transform:none}.auth-form{width:min(440px,calc(100% - 38px))}.mobile-switch{display:block}}
        @media(prefers-reduced-motion:reduce){.auth-brand,.auth-panel,.auth-form{transition:none!important}}
    </style>

    <main class="auth-stage">
        <section id="auth-shell" class="auth-shell" data-mode="{{ $mode }}">
            <aside class="auth-brand">
                <span class="orb orb-one"></span><span class="orb orb-two"></span>
                <div class="brand-mark"><span class="brand-icon">C</span> PURR'S COFFEE</div>
                <div class="brand-copy">
                    <h2 data-brand-title>{{ $mode === 'signup' ? 'Welcome back!' : 'Hey there!' }}</h2>
                    <p data-brand-copy>{{ $mode === 'signup' ? 'Your coffee workspace is ready whenever you are.' : 'You are one step away from a smoother day.' }}</p>
                </div>
                <div class="brand-switch">Accounts are created and managed by an administrator.</div>
            </aside>

            <div class="auth-panel">
                <form class="auth-form" data-form="signin" method="POST" action="{{ route('login.submit', ['role' => 'admin']) }}">
                    @csrf
                    <p class="eyebrow">Welcome back</p>
                    <h1>Sign In</h1>
                    <p class="intro">Continue to your coffee workspace.</p>

                    @if ($mode === 'signin' && $errors->any())
                        <div class="alert">Please check your sign-in details.</div>
                    @endif

                    @if (session('status'))
                        <div class="alert">{{ session('status') }}</div>
                    @endif

                    <div class="fields">
                        <div class="field">
                            <label for="signin-email">Email address</label>
                            <input id="signin-email" name="email" type="email" value="{{ $mode === 'signin' ? old('email') : '' }}" required autocomplete="email">
                            @if ($mode === 'signin')
                                @error('email') <p class="error">{{ $message }}</p> @enderror
                            @endif
                        </div>
                        <div class="field">
                            <label for="signin-password">Password</label>
                            <div class="input-wrap"><input id="signin-password" name="password" type="password" required autocomplete="current-password"><button class="password-toggle" type="button" data-password="signin-password">Show</button></div>
                        </div>
                        <label class="remember"><input type="checkbox" name="remember"> Keep me signed in</label>
                    </div>
                    <button class="submit" type="submit">Sign In</button>
                </form>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const shell = document.getElementById('auth-shell');
            shell.querySelectorAll('[data-password]').forEach(button => button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.password);
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.textContent = show ? 'Hide' : 'Show';
            }));
        });
    </script>
@endsection

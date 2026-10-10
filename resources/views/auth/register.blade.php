<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BioMed CRM &amp; PPM Operations</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans antialiased relative overflow-hidden">

    <div class="absolute -top-40 -left-40 w-96 h-96 bg-teal-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-400 text-white font-black text-2xl shadow-xl shadow-teal-500/20 mb-3">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white">Create Field Account</h1>
            <p class="text-xs text-slate-400 mt-1">Register as a Field Technician to access PPM records</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-medium space-y-1">
                @foreach ($errors->all() as $error)
                    <p><i class="fa-solid fa-circle-xmark mr-1.5 text-rose-400"></i> {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-slate-900/90 backdrop-blur-xl p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl">
            
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Full Name</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-3.5 top-3 text-slate-500 text-sm"></i>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                               placeholder="Engr. Ali Raza"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3.5 top-3 text-slate-500 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               placeholder="ali@biomed.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-3 text-slate-500 text-sm"></i>
                        <input type="password" name="password" id="reg_password_input" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <button type="button" onclick="togglePasswordVisibility('reg_password_input', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-white text-sm focus:outline-none" title="Toggle password visibility">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-3 text-slate-500 text-sm"></i>
                        <input type="password" name="password_confirmation" id="reg_confirm_password_input" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <button type="button" onclick="togglePasswordVisibility('reg_confirm_password_input', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-white text-sm focus:outline-none" title="Toggle password visibility">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-teal-600 to-emerald-500 hover:from-teal-500 hover:to-emerald-400 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-teal-600/30 transition transform active:scale-98">
                    <i class="fa-solid fa-user-check mr-2"></i> Register Account
                </button>
            </form>

            <div class="mt-5 text-center text-xs text-slate-400">
                Already registered? 
                <a href="{{ route('login') }}" class="text-teal-400 hover:text-teal-300 font-bold underline">Log In Here</a>
            </div>

        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

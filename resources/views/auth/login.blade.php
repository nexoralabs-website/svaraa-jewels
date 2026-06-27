<x-layouts.app>
    <x-slot:title>Login | Svaraa Jewels</x-slot:title>

    <div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[#FFFFF0]">
        <div class="max-w-md w-full bg-white p-10 border border-[#C8A35D]/30 shadow-xl relative">
            {{-- Decorative Element --}}
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C8A35D] via-[#6E0F12] to-[#C8A35D]"></div>

            <div class="text-center mb-8">
                <h2 class="text-3xl font-serif text-[#6E0F12]">Welcome Back</h2>
                <p class="mt-2 text-sm text-gray-600">Sign in to access your wishlist and exclusive offers.</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border transition-colors">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-medium text-gray-700 uppercase tracking-widest">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-[#C8A35D] hover:text-[#6E0F12] transition-colors">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border transition-colors">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-[#6E0F12] border-gray-300 rounded focus:ring-[#6E0F12]">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-600">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="w-full bg-[#6E0F12] text-white px-8 py-3.5 uppercase tracking-widest text-sm font-medium hover:bg-[#520b0d] transition-colors shadow-sm">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-gray-600">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-[#C8A35D] hover:text-[#6E0F12] font-medium transition-colors">
                    Create one
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>

<x-layouts.app>
    <x-slot:title>Forgot Password | Svaraa Jewels</x-slot:title>

    <div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[#FFFFF0]">
        <div class="max-w-md w-full bg-white p-10 border border-[#C8A35D]/30 shadow-xl relative">
            {{-- Decorative Element --}}
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C8A35D] via-[#6E0F12] to-[#C8A35D]"></div>

            <div class="text-center mb-6">
                <h2 class="text-3xl font-serif text-[#6E0F12]">Reset Password</h2>
                <p class="mt-4 text-sm text-gray-600 leading-relaxed">
                    Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border transition-colors">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('login') }}" class="text-[#C8A35D] hover:text-[#6E0F12] text-sm font-medium transition-colors">
                        Back to Login
                    </a>
                    <button type="submit" class="bg-[#6E0F12] text-white px-6 py-3 uppercase tracking-widest text-xs font-medium hover:bg-[#520b0d] transition-colors shadow-sm">
                        Email Password Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

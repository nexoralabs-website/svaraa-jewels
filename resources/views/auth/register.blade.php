<x-layouts.app>
    <x-slot:title>Register | Svaraa Jewels</x-slot:title>

    <div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[#FFFFF0]">
        <div class="max-w-md w-full bg-white p-10 border border-[#C8A35D]/30 shadow-xl relative">
            {{-- Decorative Element --}}
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#C8A35D] via-[#6E0F12] to-[#C8A35D]"></div>

            <div class="text-center mb-8">
                <h2 class="text-3xl font-serif text-[#6E0F12]">Create Account</h2>
                <p class="mt-2 text-sm text-gray-600">Join Svaraa Jewels for an exclusive experience.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border transition-colors">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border transition-colors">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border transition-colors">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border transition-colors">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit" class="w-full bg-[#6E0F12] text-white px-8 py-3.5 uppercase tracking-widest text-sm font-medium hover:bg-[#520b0d] transition-colors shadow-sm mt-4">
                    Create Account
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-gray-600">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-[#C8A35D] hover:text-[#6E0F12] font-medium transition-colors">
                    Sign in
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>

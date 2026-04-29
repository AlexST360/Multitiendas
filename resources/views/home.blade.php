<x-storefront-layout>
    <div class="max-w-4xl mx-auto py-16 px-6 text-center">
        <h1 class="text-4xl font-bold text-gray-900">
            Multora
        </h1>

        <p class="mt-4 text-gray-600 text-lg">
            Infraestructura privada para gestión y tiendas SaaS.
        </p>

        <div class="mt-8 flex justify-center gap-4">
            <a href="{{ route('login') }}"
               class="px-6 py-3 rounded bg-gray-900 text-white hover:bg-black transition">
                Ingresar
            </a>

            <a href="{{ route('register') }}"
               class="px-6 py-3 rounded border border-gray-300 hover:bg-gray-100 transition">
                Crear cuenta
            </a>
        </div>
    </div>
</x-storefront-layout>

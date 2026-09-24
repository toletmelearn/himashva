<x-layouts.app>
<x-slot:title>Contact Us | Himashva</x-slot:title>

<div class="max-w-4xl mx-auto px-4 py-12 grid md:grid-cols-2 gap-10">
    <div>
        <h1 class="font-display text-3xl text-brand-900 mb-4">Get in Touch</h1>
        <p class="text-brand-600 mb-6">Have a question about an order, product, or anything else? We'd love to hear from you.</p>

        <div class="space-y-2 text-sm text-brand-700 mb-6">
            <p>📧 {{ settings('contact_email') }}</p>
            <p>📞 {{ settings('contact_phone') }}</p>
            @if (settings('whatsapp_number'))
                <p>💬 <a href="https://wa.me/{{ settings('whatsapp_number') }}" target="_blank" class="underline">WhatsApp us</a></p>
            @endif
            <p>📍 {{ settings('address') }}</p>
        </div>

        <div class="bg-brand-100 rounded-xl h-48 flex items-center justify-center text-brand-400 text-sm">
            Map placeholder
        </div>
    </div>

    <form action="{{ route('contact.submit') }}" method="POST" class="bg-white border border-brand-200 rounded-xl p-6 space-y-4">
        @csrf
        <input name="name" placeholder="Your Name" required value="{{ old('name') }}" class="w-full border border-brand-300 rounded px-3 py-2 text-sm">
        <input name="email" type="email" placeholder="Email" required value="{{ old('email') }}" class="w-full border border-brand-300 rounded px-3 py-2 text-sm">
        <input name="phone" placeholder="Phone (optional)" value="{{ old('phone') }}" class="w-full border border-brand-300 rounded px-3 py-2 text-sm">
        <input name="subject" placeholder="Subject" required value="{{ old('subject') }}" class="w-full border border-brand-300 rounded px-3 py-2 text-sm">
        <textarea name="message" placeholder="Your Message" required rows="5" class="w-full border border-brand-300 rounded px-3 py-2 text-sm">{{ old('message') }}</textarea>
        <button class="w-full bg-brand-700 hover:bg-brand-800 text-white font-medium py-3 rounded-full transition">Send Message</button>
    </form>
</div>
</x-layouts.app>

<div x-data="himashvaChatbot()" x-init="init()" class="fixed bottom-24 left-6 z-40 md:bottom-6 md:right-24 md:left-auto">
    <button @click="open = !open" :class="!open ? 'pulse-subtle' : ''" class="bg-brand-700 hover:bg-brand-800 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-2xl transition-all hover:scale-110">
        <span x-show="!open">💬</span>
        <span x-show="open" x-cloak>✕</span>
    </button>

    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="absolute bottom-16 left-0 md:left-auto md:right-0 w-[340px] max-w-[90vw] h-[480px] bg-white rounded-2xl shadow-2xl border border-brand-200 flex flex-col overflow-hidden origin-bottom-right">
        <div class="bg-brand-700 text-white px-4 py-3">
            <p class="font-semibold">Himashva Assistant</p>
            <p class="text-xs text-brand-200">Ask about shipping, orders, returns &amp; more</p>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3 text-sm" x-ref="messages">
            <template x-for="(msg, i) in messages" :key="i">
                <div x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    :class="msg.from === 'bot' ? 'text-left' : 'text-right'">
                    <span :class="msg.from === 'bot' ? 'bg-brand-100 text-brand-900' : 'bg-brand-700 text-white'"
                        class="inline-block px-3 py-2 rounded-xl max-w-[85%]" x-text="msg.text"></span>
                </div>
            </template>
            <div x-show="typing" x-cloak class="text-left">
                <span class="inline-flex gap-1 bg-brand-100 px-3 py-2 rounded-xl">
                    <span class="w-1.5 h-1.5 bg-brand-500 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="w-1.5 h-1.5 bg-brand-500 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="w-1.5 h-1.5 bg-brand-500 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </span>
            </div>
        </div>

        <form @submit.prevent="send" class="border-t border-brand-200 p-3 flex gap-2">
            <input x-model="input" type="text" placeholder="Type your question..."
                class="flex-1 border border-brand-300 rounded-full px-3 py-2 text-sm focus:outline-none">
            <button type="submit" class="bg-brand-700 text-white rounded-full w-9 h-9 flex items-center justify-center active:scale-90 transition-transform">➤</button>
        </form>
    </div>
</div>

<script>
function himashvaChatbot() {
    return {
        open: false,
        input: '',
        typing: false,
        messages: [{ from: 'bot', text: 'Hi! I\'m the Himashva assistant. Ask me about shipping, payments, returns, or tracking your order.' }],
        init() {},
        async send() {
            const text = this.input.trim();
            if (!text) return;
            this.messages.push({ from: 'user', text });
            this.input = '';
            this.typing = true;
            this.$nextTick(() => this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight);

            try {
                const res = await fetch('{{ route('chatbot.respond') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: text }),
                });
                const data = await res.json();
                this.typing = false;
                this.messages.push({ from: 'bot', text: data.reply });
            } catch (e) {
                this.typing = false;
                this.messages.push({ from: 'bot', text: 'Sorry, something went wrong. Please try again.' });
            }
            this.$nextTick(() => this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight);
        }
    }
}
</script>

<div x-data="toastStore()" x-init="init()" class="fixed bottom-4 right-4 z-50 space-y-2">
  <template x-for="t in toasts" :key="t.id">
    <div x-show="true" class="rounded-lg shadow-md-up px-4 py-3 text-sm"
         :class="{
           'bg-green-50 text-green-800 border border-green-200': t.type === 'success',
           'bg-red-50 text-red-800 border border-red-200': t.type === 'error',
           'bg-blue-50 text-blue-800 border border-blue-200': t.type === 'info'
         }">
      <div class="flex items-start justify-between gap-4">
        <span x-text="t.message"></span>
        <button @click="dismiss(t.id)" class="text-xs opacity-70 hover:opacity-100">Dismiss</button>
      </div>
    </div>
  </template>
</div>
<script>
  function toastStore() {
    return {
      toasts: [],
      init() {
        window.addEventListener('notify', (e) => {
          const message = e.detail?.message ?? 'Notice';
          const type = e.detail?.type ?? 'info';
          const id = Date.now() + Math.random();
          this.toasts.push({ id, message, type });
          setTimeout(() => this.dismiss(id), 4000);
        });
      },
      dismiss(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
    };
  }
</script>

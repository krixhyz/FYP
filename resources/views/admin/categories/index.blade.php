@extends('layouts.admin')

@section('title', 'Categories')

@section('content')

{{-- Script MUST be defined before x-data so Alpine can find categoryManager --}}
<script>
var __categoryParentNames = @json($parents->pluck('name', 'id'));

function categoryManager() {
    return {
        parentNames: window.__categoryParentNames || {},
        mode: 'idle',
        expanded: [],
        formTitle: 'Create / Edit Category',

        createParentId: null,
        createParentName: '',
        ecoPointsPreview: 0,

        editAction: '',
        editParentId: null,
        editParentName: '',
        editName: '',
        editCo2: 0,
        editReuse: 0,
        editEco: 0,

        toggleParent(id) {
            if (this.expanded.includes(id)) {
                this.expanded = this.expanded.filter(i => i !== id);
            } else {
                this.expanded.push(id);
            }
        },

        openCreate(parentId) {
            this.mode = 'create';
            this.createParentId = parentId;
            this.createParentName = parentId ? (this.parentNames[parentId] ?? '') : '';
            this.ecoPointsPreview = 0;
            this.formTitle = parentId ? 'Add Subcategory' : 'New Parent Category';
        },

        openEdit(id, name, parentId, co2, reuse, eco) {
            this.mode = 'edit';
            this.editAction = '/admin/categories/' + id;
            this.editParentId = parentId;
            this.editParentName = parentId ? (this.parentNames[parentId] ?? '') : '';
            this.editName  = name;
            this.editCo2   = co2;
            this.editReuse = reuse;
            this.editEco   = eco;
            this.formTitle = 'Edit Category';
        },

        reset() {
            this.mode = 'idle';
            this.formTitle = 'Create / Edit Category';
            this.createParentId = null;
            this.ecoPointsPreview = 0;
        },

        calcEco() {
            const co2   = parseFloat(this.$refs.createCo2 ? this.$refs.createCo2.value : 0) || 0;
            const reuse = parseFloat(this.$refs.createReuse ? this.$refs.createReuse.value : 0) || 0;
            this.ecoPointsPreview = Math.round(co2 * reuse / 100 * 100) / 100;
            if (this.$refs.createEco) this.$refs.createEco.value = this.ecoPointsPreview;
        },

        calcEditEco() {
            const co2   = parseFloat(this.editCo2)  || 0;
            const reuse = parseFloat(this.editReuse) || 0;
            this.editEco = Math.round(co2 * reuse / 100 * 100) / 100;
        },
    };
}
</script>

<div
    x-data="categoryManager()"
    class="space-y-6"
>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="font-space text-[10px] font-bold uppercase tracking-widest text-[#888]">Platform Config</p>
            <h2 class="font-space font-bold text-2xl text-[#1a1c1c] mt-1">Category Management</h2>
        </div>
        <button
            x-on:click="openCreate(null)"
            class="inline-flex items-center gap-2 bg-[#006a38] text-white px-4 py-2 font-space font-bold text-xs uppercase tracking-wider rounded hover:bg-[#09864a] transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Parent Category
        </button>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-[#e8f5ee] border border-[#a7d7bd] text-[#065f46] px-4 py-3 rounded-sm text-sm font-manrope">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-[#fff5f5] border border-[#fecaca] text-[#ba1a1a] px-4 py-3 rounded-sm text-sm font-manrope">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19H19a2 2 0 001.75-2.97L13.75 4a2 2 0 00-3.5 0L3.25 16.03A2 2 0 005.07 19z"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-[#fff5f5] border border-[#fecaca] text-[#ba1a1a] px-4 py-3 rounded-sm text-sm font-manrope space-y-1">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        {{-- ── Category Tree ────────────────────────── --}}
        <div class="xl:col-span-3 space-y-3">
            @forelse($parents as $parent)
                <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] rounded-sm overflow-hidden">
                    {{-- Parent row --}}
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-[#f0f0f0]">
                        <button
                            x-on:click="toggleParent({{ $parent->id }})"
                            class="shrink-0 text-[#888] hover:text-[#006a38] transition"
                        >
                            <svg
                                class="w-4 h-4 transition-transform duration-200"
                                :class="expanded.includes({{ $parent->id }}) ? 'rotate-90' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-space font-bold text-sm text-[#1a1c1c]">{{ $parent->name }}</span>
                                <span class="text-[10px] font-bold bg-[#f0f8f5] text-[#006a38] px-2 py-0.5 rounded-full">
                                    {{ $parent->children->count() }} sub
                                </span>
                                @if($parent->products_count > 0)
                                    <span class="text-[10px] font-bold bg-[#e2e8f0] text-[#475569] px-2 py-0.5 rounded-full">
                                        {{ $parent->products_count }} products
                                    </span>
                                @endif
                            </div>
                            <div class="flex gap-3 mt-0.5 text-[11px] text-[#888]">
                                <span>CO₂ {{ $parent->base_co2_kg }} kg</span>
                                <span>Reuse {{ $parent->reuse_pct }}%</span>
                                <span>Eco {{ $parent->eco_points }} pts</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <button
                                x-on:click="openCreate({{ $parent->id }})"
                                title="Add subcategory"
                                class="inline-flex items-center gap-1 text-[10px] font-bold text-[#006a38] border border-[#006a38] px-2 py-1 rounded hover:bg-[#f0f8f5] transition"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Sub
                            </button>
                            <button
                                x-on:click="openEdit({{ $parent->id }}, '{{ addslashes($parent->name) }}', null, {{ (float)$parent->base_co2_kg }}, {{ (float)$parent->reuse_pct }}, {{ (float)$parent->eco_points }})"
                                title="Edit"
                                class="p-1.5 text-[#444] hover:text-[#006a38] hover:bg-[#f0f8f5] rounded transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('admin.categories.destroy', $parent) }}" onsubmit="return confirm('Delete \'{{ addslashes($parent->name) }}\' and all its subcategories? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete" class="p-1.5 text-[#ba1a1a] hover:bg-[#fff5f5] rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Subcategory rows — x-show only, no x-collapse --}}
                    <div x-show="expanded.includes({{ $parent->id }})" style="display:none">
                        @forelse($parent->children as $sub)
                            <div class="flex items-center gap-3 px-4 py-2.5 border-b border-[#f9f9f9] bg-[#fafafa] hover:bg-[#f5f5f5] transition last:border-b-0">
                                <div class="w-4 shrink-0 flex justify-center">
                                    <svg class="w-3 h-3 text-[#ccc]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-manrope font-semibold text-sm text-[#1a1c1c]">{{ $sub->name }}</span>
                                        @if($sub->products_count > 0)
                                            <span class="text-[10px] font-bold bg-[#e2e8f0] text-[#475569] px-2 py-0.5 rounded-full">{{ $sub->products_count }} products</span>
                                        @endif
                                    </div>
                                    <div class="flex gap-3 mt-0.5 text-[11px] text-[#888]">
                                        <span>CO₂ {{ $sub->base_co2_kg }} kg</span>
                                        <span>Reuse {{ $sub->reuse_pct }}%</span>
                                        <span>Eco {{ $sub->eco_points }} pts</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button
                                        x-on:click="openEdit({{ $sub->id }}, '{{ addslashes($sub->name) }}', {{ $parent->id }}, {{ (float)$sub->base_co2_kg }}, {{ (float)$sub->reuse_pct }}, {{ (float)$sub->eco_points }})"
                                        title="Edit"
                                        class="p-1.5 text-[#444] hover:text-[#006a38] hover:bg-[#f0f8f5] rounded transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $sub) }}" onsubmit="return confirm('Delete \'{{ addslashes($sub->name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Delete" class="p-1.5 text-[#ba1a1a] hover:bg-[#fff5f5] rounded transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="px-8 py-3 text-xs text-[#888] font-manrope italic">No subcategories yet. Click "+ Sub" to add one.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] p-10 text-center rounded-sm">
                    <svg class="w-12 h-12 text-[#ddd] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    <p class="font-space font-bold text-sm text-[#444]">No categories yet</p>
                    <p class="font-manrope text-xs text-[#888] mt-1">Create your first parent category to get started.</p>
                </div>
            @endforelse
        </div>

        {{-- ── Create / Edit Panel ────────────────── --}}
        <div class="xl:col-span-2">
            <div class="bg-white shadow-[0_4px_24px_rgba(26,28,28,0.07)] rounded-sm p-5 sticky top-6">
                <h3 class="font-space font-bold text-base text-[#1a1c1c] mb-4" x-text="formTitle"></h3>

                {{-- CREATE form --}}
                <form
                    x-show="mode === 'create'"
                    method="POST"
                    action="{{ route('admin.categories.store') }}"
                    class="space-y-4"
                    style="display:none"
                >
                    @csrf
                    <input type="hidden" name="parent_id" :value="createParentId">

                    <div>
                        <label class="block font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">
                            <span x-text="createParentId ? 'Subcategory Name' : 'Parent Category Name'">Name</span> *
                        </label>
                        <input
                            type="text" name="name"
                            value="{{ old('name') }}"
                            placeholder="e.g. Denim Jackets"
                            required minlength="2" maxlength="100"
                            class="w-full border border-gray-300 px-3 py-2 font-manrope text-sm focus:border-[#006a38] focus:outline-none rounded-sm"
                        >
                    </div>

                    <div x-show="createParentId" class="text-xs text-[#888] font-manrope bg-[#f9f9f9] px-3 py-2 rounded-sm" style="display:none">
                        Adding subcategory under: <strong x-text="createParentName"></strong>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">CO₂ Saved (kg) *</label>
                            <input type="number" name="base_co2_kg" step="0.01" min="0"
                                value="{{ old('base_co2_kg', 0) }}"
                                x-on:input="calcEco()"
                                x-ref="createCo2"
                                required
                                class="w-full border border-gray-300 px-3 py-2 font-manrope text-sm focus:border-[#006a38] focus:outline-none rounded-sm">
                        </div>
                        <div>
                            <label class="block font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Reuse % *</label>
                            <input type="number" name="reuse_pct" step="0.01" min="0" max="100"
                                value="{{ old('reuse_pct', 0) }}"
                                x-on:input="calcEco()"
                                x-ref="createReuse"
                                required
                                class="w-full border border-gray-300 px-3 py-2 font-manrope text-sm focus:border-[#006a38] focus:outline-none rounded-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">
                            Eco Points <span class="normal-case font-normal text-[#aaa]">(auto-calculated)</span>
                        </label>
                        <input type="number" name="eco_points" step="0.01" min="0"
                            x-ref="createEco"
                            :value="ecoPointsPreview"
                            class="w-full border border-gray-300 px-3 py-2 font-manrope text-sm focus:border-[#006a38] focus:outline-none rounded-sm bg-[#f9f9f9]">
                        <p class="text-[10px] text-[#aaa] mt-1">= CO₂ kg × Reuse % ÷ 100</p>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 bg-[#006a38] text-white py-2 font-space font-bold text-xs uppercase tracking-wider rounded hover:bg-[#09864a] transition">Create</button>
                        <button type="button" x-on:click="reset()" class="px-4 border border-gray-300 text-[#444] py-2 font-space text-xs rounded hover:bg-[#f9f9f9] transition">Clear</button>
                    </div>
                </form>

                {{-- EDIT form --}}
                <form
                    x-show="mode === 'edit'"
                    method="POST"
                    :action="editAction"
                    class="space-y-4"
                    style="display:none"
                >
                    @csrf
                    @method('PUT')

                    <div x-show="editParentId !== null" class="text-xs text-[#888] font-manrope bg-[#f9f9f9] px-3 py-2 rounded-sm" style="display:none">
                        Subcategory under: <strong x-text="editParentName"></strong>
                    </div>

                    <div>
                        <label class="block font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Name *</label>
                        <input type="text" name="name" x-model="editName"
                            required minlength="2" maxlength="100"
                            class="w-full border border-gray-300 px-3 py-2 font-manrope text-sm focus:border-[#006a38] focus:outline-none rounded-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">CO₂ Saved (kg) *</label>
                            <input type="number" name="base_co2_kg" step="0.01" min="0"
                                x-model="editCo2" x-on:input="calcEditEco()"
                                required
                                class="w-full border border-gray-300 px-3 py-2 font-manrope text-sm focus:border-[#006a38] focus:outline-none rounded-sm">
                        </div>
                        <div>
                            <label class="block font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">Reuse % *</label>
                            <input type="number" name="reuse_pct" step="0.01" min="0" max="100"
                                x-model="editReuse" x-on:input="calcEditEco()"
                                required
                                class="w-full border border-gray-300 px-3 py-2 font-manrope text-sm focus:border-[#006a38] focus:outline-none rounded-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block font-space text-[10px] font-bold uppercase tracking-widest text-[#888] mb-1">
                            Eco Points <span class="normal-case font-normal text-[#aaa]">(auto-calculated)</span>
                        </label>
                        <input type="number" name="eco_points" step="0.01" min="0"
                            x-model="editEco"
                            class="w-full border border-gray-300 px-3 py-2 font-manrope text-sm focus:border-[#006a38] focus:outline-none rounded-sm bg-[#f9f9f9]">
                        <p class="text-[10px] text-[#aaa] mt-1">= CO₂ kg × Reuse % ÷ 100</p>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 bg-[#006a38] text-white py-2 font-space font-bold text-xs uppercase tracking-wider rounded hover:bg-[#09864a] transition">Save Changes</button>
                        <button type="button" x-on:click="reset()" class="px-4 border border-gray-300 text-[#444] py-2 font-space text-xs rounded hover:bg-[#f9f9f9] transition">Cancel</button>
                    </div>
                </form>

                {{-- Idle state --}}
                <div x-show="mode === 'idle'" class="flex flex-col items-center justify-center py-10 text-center">
                    <svg class="w-10 h-10 text-[#ddd] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    <p class="font-manrope text-sm text-[#888]">Select an action from the list,<br>or create a new category.</p>
                </div>
            </div>

            {{-- Eco Metrics Guide --}}
            <div class="mt-4 bg-[#f0f8f5] border border-[#a7d7bd] rounded-sm p-4 text-xs font-manrope text-[#065f46] space-y-1">
                <p class="font-bold font-space uppercase tracking-widest text-[10px] text-[#006a38] mb-2">Eco Metrics Guide</p>
                <p><strong>CO₂ Saved (kg):</strong> Estimated CO₂ emissions avoided per reused item in this category.</p>
                <p><strong>Reuse %:</strong> Percentage representing how effectively this category reduces waste vs. buying new.</p>
                <p><strong>Eco Points:</strong> Score awarded to users (= CO₂ × Reuse% ÷ 100). Auto-calculated but overridable.</p>
            </div>
        </div>

    </div>
</div>
@endsection

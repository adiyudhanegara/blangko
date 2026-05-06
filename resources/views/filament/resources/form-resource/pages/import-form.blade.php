<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Instructions & template download --}}
        <x-filament::section>
            <x-slot name="heading">How to use</x-slot>

            <ol style="list-style: decimal; padding-left: 1.25rem; font-size: 0.875rem; color: rgb(107 114 128); display: flex; flex-direction: column; gap: 0.25rem;">
                <li>Download the blank template below.</li>
                <li>Fill in the <strong style="font-weight: 600; color: rgb(75 85 99);">form</strong> sheet — title, language, status, etc.</li>
                <li>Add one row per question to the <strong style="font-weight: 600; color: rgb(75 85 99);">questions</strong> sheet.</li>
                <li>For radio / checkbox / select questions, add their choices to the <strong style="font-weight: 600; color: rgb(75 85 99);">options</strong> sheet.</li>
                <li>Optionally fill in the <strong style="font-weight: 600; color: rgb(75 85 99);">export_template</strong> sheet to configure the Excel export layout.</li>
                <li>Upload the completed file and click <em>Import Form</em>.</li>
            </ol>

            <div style="margin-top: 1rem;">
                <a
                    href="{{ route('admin.form-import-template') }}"
                    style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; font-weight: 500; color: rgb(234 179 8); text-decoration: none;"
                    onmouseover="this.style.opacity='0.8'"
                    onmouseout="this.style.opacity='1'"
                >
                    &#x2193; Download blank template (.xlsx)
                </a>
            </div>
        </x-filament::section>

        {{-- Upload form --}}
        <x-filament::section>
            <x-slot name="heading">Upload completed template</x-slot>

            {{ $this->form }}

            @if (!empty($this->importErrors))
                <div style="margin-top: 1rem; border-radius: 0.5rem; border: 1px solid rgb(254 202 202); background: rgb(254 242 242); padding: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 600; color: rgb(153 27 27); margin-bottom: 0.5rem;">
                        {{ count($this->importErrors) }} error(s) — fix the file and re-upload:
                    </p>
                    <ul style="list-style: disc; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.125rem; font-size: 0.875rem; color: rgb(185 28 28);">
                        @foreach ($this->importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                <a
                    href="{{ \App\Filament\Resources\FormResource::getUrl('index') }}"
                    style="font-size: 0.875rem; color: rgb(107 114 128); text-decoration: none;"
                    onmouseover="this.style.color='rgb(55 65 81)'"
                    onmouseout="this.style.color='rgb(107 114 128)'"
                >
                    Cancel
                </a>

                <x-filament::button
                    wire:click="import"
                    wire:loading.attr="disabled"
                    wire:target="import"
                >
                    <span wire:loading.remove wire:target="import">Import Form</span>
                    <span wire:loading wire:target="import">Importing…</span>
                </x-filament::button>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>

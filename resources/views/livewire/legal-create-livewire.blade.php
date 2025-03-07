<form wire:submit.prevent="submit">
    @csrf

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label"
                       for="validationTooltip01">{{ __('app.legal.title-en') }}</label>
                <input type="text" class="form-control"
                        wire:model="title_en"
                       value="{{ old('title_en') }}" id="validationTooltip01" required>
                @error('title_en')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label"
                       for="validationTooltip02">{{ __('app.legal.title-ar') }}</label>
                <input type="text" class="form-control"
                      wire:model="title_ar"
                       value="{{ old('title_ar') }}" id="validationTooltip02" required>
                @error('title_ar')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label"
                       for="validationTooltip03">{{ __('app.legal.content-en') }}</label>
                <div>
                    <textarea required wire:model="content_en" class="form-control" rows="3" id="validationTooltip03" >{{ old('content_en') }}</textarea>
                    @error('content_en')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label"
                       for="validationTooltip04">{{ __('app.legal.content-ar') }}</label>
                <div>
                    <textarea required wire:model="content_ar" class="form-control" rows="3" id="validationTooltip04" >{{ old('content_ar') }}</textarea>
                    @error('content_ar')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label"
                       for="validationTooltip17">{{ __('app.type') }}</label>
                <select class="form-select" wire:model="type" id="validationTooltip17">
                    <option value="">{{ __('app.select-one') }}</option>
                    <option value="1">{{ __('app.legal.privacy-and-policy') }}</option>
                    <option value="2">{{ __('app.legal.term-of-service') }}</option>

                </select>
                @error('type')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>



    <button type="button" wire:click="addTerm" class="btn btn-success">{{ __('app.legal.add_term') }}</button>


    @foreach($terms as $index => $term)
        <div class="row">
            <div class="col-md-5">
                <div class="mb-3">
                    <label class="form-label" for="term_content_en_{{ $index }}">{{ __('app.legal.term_content-en') }}</label>
                    <input type="text" class="form-control" wire:model="terms.{{ $index }}.content_en" id="term_content_en_{{ $index }}">
                    @error("terms.$index.content_en")
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-5">
                <div class="mb-3">
                    <label class="form-label" for="term_content_ar_{{ $index }}">{{ __('app.legal.term_content-ar') }}</label>
                    <input type="text" class="form-control" wire:model="terms.{{ $index }}.content_ar" id="term_content_ar_{{ $index }}">
                    @error("terms.$index.content_ar")
                    <div class="text-danger">{{ $message }}</div>
                    @enderror

                </div>
            </div>
            <div class="col-md-2">
                <div class="mt-4">
                <button type="button" wire:click="removeTerm({{ $index }})" class="btn btn-danger">{{ __('app.delete') }}</button>
                </div>
            </div>
        </div>
    @endforeach

    <!-- End of your form -->

    <div style="text-align: end">
        <button class="btn btn-primary" type="submit">{{ __('app.create') }}</button>
    </div>
</form>

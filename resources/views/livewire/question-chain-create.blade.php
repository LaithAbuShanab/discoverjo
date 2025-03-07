<form wire:submit.prevent="submit">
    @csrf
    <h4 class="text-center mb-4">{{ __('app.General Information for Current Question') }}</h4>
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="form-group">
                <label for="current_question" class="form-label">{{ __('app.current-question') }}</label>
                <select id="current_question" class="form-control" name="current_question" wire:model="current_question">
                    <option value="">{{ __('app.select-one') }}</option>
                    @foreach ($questions as $question)
                        <option value="{{ $question->id }}">{{ $question->question }}</option>
                    @endforeach
                </select>
                @error('current_question')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="type" class="form-label">{{ __('app.type') }}</label>
                <select id="type" class="form-control" name="type" wire:change="onTypeChange" wire:model="type">
                    <option value="">{{ __('Select Type') }}</option>
                    <option value="category">{{ __('app.category') }}</option>
                    <option value="subcategory">{{ __('app.subcategory') }}</option>
                    <option value="tag">{{ __('app.tag') }}</option>
                    <option value="feature">{{ __('app.features') }}</option>
                    <option value="cost">{{ __('app.cost') }}</option>
                    <option value="rating">{{ __('app.rating') }}</option>
                    <option value="region">{{ __('app.regions') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="value" class="form-label">{{ __('app.value') }}</label>
                @if ($type && $type != 'cost' && $type != 'rating')
                    <select id="value" class="form-control" name="value" wire:model="value">
                        <option value="">{{ __('Select Value') }}</option>
                        @foreach ($valueOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" id="value" class="form-control" name="value" placeholder="{{ __('Enter Value') }}" wire:model="value">
                @endif
                @error('value')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <hr />

    <h4 class="text-center mb-4">{{ __('app.Required Next Question for First Answers') }}</h4>
    @foreach ($requiredAnswers as $index => $answer)
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="requiredAnswers_{{ $index }}_answer" class="form-label">{{ __('app.answer') }}</label>
                    <input type="text" id="requiredAnswers_{{ $index }}_answer" class="form-control" value="{{ $answer['answer'] }}" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="requiredAnswers_{{ $index }}_next_question_id" class="form-label">{{ __('app.next-question') }}</label>
                    <select id="requiredAnswers_{{ $index }}_next_question_id" class="form-control" wire:model="requiredAnswers.{{ $index }}.next_question_id">
                        <option value="">{{ __('app.select-one') }}</option>
                        @foreach ($questions as $question)
                            <option value="{{ $question->id }}">{{ $question->question }}</option>
                        @endforeach
                    </select>
                    @error("requiredAnswers.{$index}.next_question_id")
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    @endforeach

    <hr />

    <h4 class="text-center mb-4">{{ __('app.Add More Answers and Questions') }}</h4>
    @foreach ($additionalAnswers as $index => $answer)
        <div class="row mb-3 align-items-end">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="additionalAnswers_{{ $index }}_answer" class="form-label">{{ __('app.select-answer') }}</label>
                    <select id="additionalAnswers_{{ $index }}_answer" class="form-control" wire:model="additionalAnswers.{{ $index }}.answer">
                        <option value="">{{ __('app.select-one') }}</option>
                        <option value="yes">{{ __('yes') }}</option>
                        <option value="no">{{ __('no') }}</option>
                        <option value="i_dont_know">{{ __('i_dont_know') }}</option>
                    </select>
                    @error("additionalAnswers.{$index}.answer")
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-5">
                <div class="form-group">
                    <label for="additionalAnswers_{{ $index }}_next_question_id" class="form-label">{{ __('app.next-question') }}</label>
                    <select id="additionalAnswers_{{ $index }}_next_question_id" class="form-control" wire:model="additionalAnswers.{{ $index }}.next_question_id">
                        <option value="">{{ __('app.select-one') }}</option>
                        @foreach ($questions as $question)
                            <option value="{{ $question->id }}">{{ $question->question }}</option>
                        @endforeach
                    </select>
                    @error("additionalAnswers.{$index}.next_question_id")
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger w-100" wire:click="removeAnswer({{ $index }})">{{ __('app.remove') }}</button>
            </div>
        </div>
    @endforeach

    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <button type="button" class="btn btn-secondary" wire:click="addAnswer">{{ __('app.add-answer') }}</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-end">
            <button class="btn btn-primary" type="submit">{{ __('app.create') }}</button>
        </div>
    </div>
</form>

<?php

namespace App\Repositories\Api\User;


use App\Http\Resources\PlaceResource;
use App\Http\Resources\QuestionResource;
use App\Interfaces\Gateways\Api\User\GameApiRepositoryInterface;
use App\Models\Place;
use App\Models\Question;
use App\Models\QuestionChain;
use App\Models\UserAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EloquentGameApiRepository implements GameApiRepositoryInterface
{

    public function start()
    {
        $question = $this->getRandomFirstQuestion();
        if ($question) {
            return new QuestionResource($question);
        }
    }

    public function next($data)
    {
        $userId = Auth::guard('api')->user()->id;
        $userAnswer = UserAnswer::where('user_id', $userId)->first();

        $placesOfPastAnswer = $this->getPlacesOfPastAnswer($userAnswer);

        $questionDetail = $this->getQuestionDetail($data['question_id'], $data['answer']);
        if (!$questionDetail) {
            return $this->handleNoQuestionDetail($userAnswer, $placesOfPastAnswer);
        }

        $this->updateUserAnswer($userId, $questionDetail, $data['answer']);

        $placesOfWithCurrentAnswer = $this->findPlaces($this->getUserAnswers($userId));
        if (count($placesOfWithCurrentAnswer) == 0) {
            return $this->handleNoPlacesWithCurrentAnswer($userAnswer, $placesOfPastAnswer);
        }

        return $this->getNextQuestionOrPlace($data['question_id'],$data['answer'], $placesOfWithCurrentAnswer,$userAnswer);
    }

    public function finish()
    {
        $userId = Auth::guard('api')->user()->id;
        $userAnswer = UserAnswer::where('user_id', $userId)->first();
        if ($userAnswer) {
            return $this->handleFinishWithAnswer($userAnswer);
        } else {
            throw new HttpException(400, 'You did not start the game to finish');
        }
    }

    private function getRandomFirstQuestion()
    {
        return Question::where('is_first_question', '1')
            ->whereHas('questionChain')
            ->inRandomOrder()
            ->first();
    }

    private function getPlacesOfPastAnswer($userAnswer)
    {
        if ($userAnswer) {
            return $this->findPlaces($userAnswer->answers);
        }
        return $this->findPlaces(json_encode(['category' => '1']));
    }

    private function getQuestionDetail($questionId, $answer)
    {
        return QuestionChain::where('question_id', $questionId)
            ->where('answer', $answer)
            ->first();
    }

    private function handleNoQuestionDetail($userAnswer, $placesOfPastAnswer)
    {
        $userAnswer->delete();
        $randomPlace = $this->getRandomPlace($placesOfPastAnswer);
        return new PlaceResource(Place::find($randomPlace->id));
    }

    private function updateUserAnswer($userId, $questionDetail, $answer)
    {
        $questionType = ($answer == 'yes' || $answer == "i_dont_know") ? $questionDetail->type : '!' . $questionDetail->type;
        $questionValue = $questionDetail->value;

        $userAnswer = UserAnswer::where('user_id', $userId)->first();
        if (!$userAnswer) {
            UserAnswer::create([
                'user_id' => $userId,
                'answers' => json_encode([$questionType => $questionValue]),
            ]);
        } else {
            $currentAnswers = json_decode($userAnswer->answers, true);
            $currentAnswers[$questionType] = $questionValue;
            $userAnswer->update(['answers' => json_encode($currentAnswers)]);
        }
    }

    private function getUserAnswers($userId)
    {
        return UserAnswer::where('user_id', $userId)->value('answers');
    }

    private function handleNoPlacesWithCurrentAnswer($userAnswer, $placesOfPastAnswer)
    {
        $userAnswer->delete();
        $randomPlace = $this->getRandomPlace($placesOfPastAnswer);
        return new PlaceResource(Place::find($randomPlace->id));
    }

    private function getNextQuestionOrPlace($currentQuestionId, $answer, $placesOfCurrentAnswer, $userAnswer)
    {
        // Find the next question IDs related to the current question and answer
        $nextQuestionIds = QuestionChain::where('question_id', $currentQuestionId)
            ->where('answer', $answer)
            ->pluck('next_question_id')
            ->toArray();

        shuffle($nextQuestionIds); // Shuffle to randomize the selection

        // Helper function to check if a question can lead to a place for any of the possible answers
        $canProvidePlace = function ($questionId) {
            $possibleAnswers = ['yes', 'no', "i_dont_know"];
            foreach ($possibleAnswers as $possibleAnswer) {
                $questionChain = QuestionChain::where('question_id', $questionId)
                    ->where('answer', $possibleAnswer)
                    ->first();

                if ($questionChain) {
                    $place = $this->findPlaces(json_encode([$questionChain->type => $questionChain->value]));
                    if (!$place->isEmpty()) {
                        return true;
                    }
                }
            }
            return false;
        };

        // Check if any of the next questions lead to a place for any possible answer
        foreach ($nextQuestionIds as $nextQuestionId) {
            if ($canProvidePlace($nextQuestionId)) {
                $question = Question::find($nextQuestionId);
                if ($question) {
                    return new QuestionResource($question);
                }
            }
        }

        // If no valid next question is found, return a place based on the previous user answers
        $places = $placesOfCurrentAnswer;
        if (!$places->isEmpty()) {
            $placesArray = $places->toArray(); // Convert to array
            shuffle($placesArray); // Shuffle to randomize the selection
            $place = $placesArray[0]; // Get the first place from the shuffled array
            $userAnswer->delete();
            return new PlaceResource(Place::find($place->id)); // Use the Place object with the ID
        }

        // No place found, finish the game
        throw new HttpException(404, "No question could provide a place based on the current answers.");
    }

    private function handleFinishWithAnswer($userAnswer)
    {
        $placesAnswer = $this->findPlaces($userAnswer->answers);
        $randomPlace = $this->getRandomPlace($placesAnswer);
        $userAnswer->delete();
        return new PlaceResource(Place::find($randomPlace->id));
    }

    private function getRandomPlace($places)
    {
        $placesArray = $places->toArray();
        shuffle($placesArray);
        return $placesArray[0];
    }


    function findPlaces($userInput)
    {
        // Decode the JSON input
        $filters = json_decode($userInput, true);

        // Start building the query
        $query = DB::table('places')
            ->join('place_categories', 'places.id', '=', 'place_categories.place_id')
            ->join('categories', 'place_categories.category_id', '=', 'categories.id')
            ->join('regions', 'places.region_id', '=', 'regions.id')
            ->leftJoin('feature_place', 'places.id', '=', 'feature_place.place_id')
            ->leftJoin('taggables', function ($join) {
                $join->on('places.id', '=', 'taggables.taggable_id')
                    ->where('taggables.taggable_type', '=', 'App\\Models\\Place');
            });

        // Apply category filters (if any)
        if (isset($filters['category'])) {
            $query->where('categories.parent_id', $filters['category']);
        }
        if (isset($filters['!category'])) {
            $query->where('categories.parent_id', '!=', $filters['!category']);
        }

        // Apply subcategory filters (if any)
        if (isset($filters['subcategory'])) {
            $query->whereIn('place_categories.category_id', [$filters['subcategory']]);
        }
        if (isset($filters['!subcategory'])) {
            $query->whereNotIn('place_categories.category_id', [$filters['!subcategory']]);
        }

        // Apply feature filters (if any)
        if (isset($filters['feature'])) {
            $query->where('feature_place.feature_id', $filters['feature']);
        }
        if (isset($filters['!feature'])) {
            $query->where('feature_place.feature_id', '!=', $filters['!feature']);
        }

        // Apply tag filters (if any)
        if (isset($filters['tag'])) {
            $query->where('taggables.tag_id', $filters['tag']);
        }
        if (isset($filters['!tag'])) {
            $query->where('taggables.tag_id', '!=', $filters['!tag']);
        }

        // Apply region filter (if any)
        if (isset($filters['region'])) {
            $query->where('places.region_id', $filters['region']);
        }
        if (isset($filters['!region'])) {
            $query->where('places.region_id', '!=', $filters['!region']);
        }

        // Apply cost filter (if any)
        if (isset($filters['cost'])) {
            $query->where('places.price_level', $filters['cost']);
        }
        if (isset($filters['!cost'])) {
            $query->where('places.price_level', '!=', $filters['!cost']);
        }

        // Apply rating filter (if any)
        if (isset($filters['rating'])) {
            $query->where('places.rating', '>=', $filters['rating']);
        }
        if (isset($filters['!rating'])) {
            $query->where('places.rating', '<', $filters['!rating']);
        }

        // Select necessary columns (or all)
        $query->select('places.id');

        // Execute the query and get the results
        $places = $query->distinct()->get();


        return $places;
    }
}

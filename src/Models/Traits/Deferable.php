<?php 
namespace Ry\Admin\Models\Traits;

use Carbon\Carbon;
use Ry\Admin\Models\Timeline;

trait Deferable
{
    public function saveAt($datetime) {
        if(!($datetime instanceof Carbon))
            $datetime = Carbon::parse($datetime);
        //keep it into timeline
        $timeline = new Timeline();
        $timeline->serializable_type = get_class($this);
        $timeline->active = false;
        $timeline->save_at = $datetime;
        if($datetime->isPast() || $datetime->isSameAs('Y-m-d', Carbon::now())) {
            if($this->id) {
                //get active date
                $active_timeline = Timeline::whereSerializableType(get_class($this))->whereSerializableId($this->id)->whereActive(1)->first();
                if($active_timeline) {
                    if($active_timeline->save_at && $active_timeline->delete_at && $datetime->isBetween($active_timeline->save_at, $active_timeline->delete_at)) {
                        $active_timeline->active = false;
                        $active_timeline->save();
                        $timeline->active = true;
                        $timeline->action = 'updated';
                        $timeline->revert_id = $active_timeline->id;
                        //save the model immediately
                        $this->save();
                    }
                    elseif($active_timeline->save_at && $datetime->isAfter($active_timeline->save_at)) {
                        $active_timeline->active = false;
                        $active_timeline->save();
                        $timeline->active = true;
                        $timeline->action = 'updated';
                        $timeline->revert_id = $active_timeline->id;
                        //save the model immediately
                        $this->save();
                    }
                }
                else {
                    $timeline->active = true;
                    $timeline->action = 'updated';
                    //save the model immediately
                    $this->save();
                    
                }
            }
            else {
                $timeline->active = true;
                $timeline->action = 'created';
                //save the model immediately
                $this->save();
            }
        }
        $timeline->serializable_id = $this->id;
        $timeline->nsetup = $this->toArray();
        $timeline->save();
    }
    
    public function deleteAt($datetime) {
        if(!($datetime instanceof Carbon))
            $datetime = Carbon::parse($datetime);
        //keep it into timeline
        $timeline = new Timeline();
        $timeline->serializable_type = get_class($this);
        $timeline->delete_at = $datetime;
        $timeline->active = false;
        if($datetime->isFuture() || $datetime->isSameAs('Y-m-d H:i:s', Carbon::now())) {
            if($this->id) {
                //get active date
                $active_timeline = Timeline::whereSerializableType(get_class($this))->whereSerializableId($this->id)->whereActive(1)->first();
                if($active_timeline) {
                    if($active_timeline->save_at && $active_timeline->delete_at && $datetime->isBetween($active_timeline->save_at, $active_timeline->delete_at)) {
                        $active_timeline->active = false;
                        $active_timeline->save();
                        $timeline->active = true;
                        $timeline->action = 'updated';
                        $timeline->revert_id = $active_timeline->id;
                        //save the model immediately
                        $this->save();
                    }
                    elseif($active_timeline->delete_at && $datetime->isBefore($active_timeline->delete_at)) {
                        $active_timeline->active = false;
                        $active_timeline->save();
                        $timeline->active = true;
                        $timeline->action = 'updated';
                        $timeline->revert_id = $active_timeline->id;
                        //save the model immediately
                        $this->save();
                    }
                }
                else {
                    $timeline->action = 'updated';
                    $timeline->active = true;
                    //save the model immediately
                    $this->save();
                }
            }
            else {
                $timeline->action = 'created';
                $timeline->active = true;
                //save the model immediately
                $this->save();
            }
        }
        $timeline->serializable_id = $this->id;
        $timeline->nsetup = $this->toArray();
        $timeline->save();
    }
    
    public function saveBetween($start, $end) {
        if(!($start instanceof Carbon))
            $start = Carbon::parse($start);
        if(!($end instanceof Carbon))
            $end = Carbon::parse($end);
        //keep it into timeline
        $timeline = new Timeline();
        $timeline->serializable_type = get_class($this);
        $timeline->save_at = $start;
        $timeline->delete_at = $end;
        $timeline->active = false;
        if(($start->isPast() || $start->isSameAs('Y-m-d', Carbon::now())) && ($end->isFuture() || $end->isSameAs('Y-m-d', Carbon::now()))) {
            if($this->id) {
                //get active date
                $active_timeline = Timeline::whereSerializableType(get_class($this))->whereSerializableId($this->id)->whereActive(1)->first();
                if($active_timeline) {
                    if($active_timeline->save_at && $active_timeline->delete_at && ($start->isBetween($active_timeline->save_at, $active_timeline->delete_at) || $end->isBetween($active_timeline->save_at, $active_timeline->delete_at))) {
                        $active_timeline->active = false;
                        $active_timeline->save();
                        $timeline->active = true;
                        $timeline->action = 'updated';
                        $timeline->revert_id = $active_timeline->id;
                        //save the model immediately
                        $this->save();
                    }
                    elseif($active_timeline->save_at && $start->isAfter($active_timeline->save_at)) {
                        $active_timeline->active = false;
                        $active_timeline->save();
                        $timeline->active = true;
                        $timeline->action = 'updated';
                        $timeline->revert_id = $active_timeline->id;
                        //save the model immediately
                        $this->save();
                    }
                }
                else {
                    $timeline->action = 'updated';
                    $timeline->active = true;
                    //save the model immediately
                    $this->save();
                }
            }
            else {
                $timeline->action = 'created';
                $timeline->active = true;
                //save the model immediately
                $this->save();
            }
        }
        else {
            $this->save();
        }
        $timeline->serializable_id = $this->id;
        $timeline->nsetup = $this->toArray();
        $timeline->save();
    }
    
    public function timelines() {
        return $this->morphMany(Timeline::class, "serializable");
    }
}
?>
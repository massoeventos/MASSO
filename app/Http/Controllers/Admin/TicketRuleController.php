<?php

namespace Masso\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Masso\Event;
use Masso\EventTicket;
use Masso\Log;
use Masso\Services\Checkout\TicketRuleEvaluator;
use Masso\TicketRule;

/**
 * Reglas de compra de un evento (/eventos/{id}/reglas): lista, alta y baja,
 * más una vista agrupada de solo lectura (categorías con sus tickets).
 */
class TicketRuleController extends AdminController
{
    public function index($id)
    {
        $event = Event::findOrFail($id);
        $tickets = $event->tickets()->with('categories', 'allowedWith')->get();
        $categories = $event->ticketCategories()->get();
        $rules = $event->ticketRules()->get();
        $name = (new TicketRuleEvaluator($event))->namer();

        $groups = $categories->map(fn ($category) => [
            'category' => $category,
            'tickets' => $tickets->filter(fn ($t) => $t->categories->contains('id', $category->id))->values(),
        ])->values();
        $uncategorized = $tickets->filter(fn ($t) => $t->categories->isEmpty())->values();
        if ($uncategorized->isNotEmpty()) {
            $groups->push(['category' => null, 'tickets' => $uncategorized]);
        }

        // Reglas que nombran la categoría o alguno de sus tickets.
        $rulesFor = fn ($group) => $rules->filter(fn ($rule) =>
            ($group['category'] && $rule->mentions(TicketRule::TYPE_CATEGORY, $group['category']->id))
            || $group['tickets']->contains(fn ($t) => $rule->mentions(TicketRule::TYPE_TICKET, $t->id)))->values();
        // Reglas solo por tipo (principales / complementarios): valen en todas.
        $kindRules = $rules->filter(fn ($rule) => $rule->subject_type === TicketRule::TYPE_KIND
            && in_array($rule->target_type, [null, TicketRule::TYPE_KIND], true))->values();

        return view('admin.general.events.rules', [
            'title' => 'Reglas de compra: ' . $event->name,
            'event' => $event,
            'tickets' => $tickets,
            'categories' => $categories,
            'rules' => $rules,
            'groups' => $groups,
            'rulesFor' => $rulesFor,
            'kindRules' => $kindRules,
            'name' => $name,
            'usesEntries' => $event->usesEntries(),
        ]);
    }

    public function store(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $options = $this->options($event);

        $data = $request->validate([
            'scope' => ['required', Rule::in(TicketRule::SCOPES)],
            'subject' => ['required', Rule::in($options)],
            'rule' => ['required', Rule::in(TicketRule::RULES)],
            'target' => ['nullable', 'required_if:rule,' . TicketRule::REQUIRES . ',' . TicketRule::EXCLUDES, Rule::in($options), 'different:subject'],
            'quantity' => ['nullable', 'required_if:rule,' . TicketRule::MAX . ',' . TicketRule::MIN, 'integer', 'min:' . ($request->input('rule') === TicketRule::MAX ? 0 : 1), 'max:100'],
        ], [
            'target.required_if' => 'Elija el objetivo de la regla (requiere / no se combina).',
            'quantity.required_if' => 'Indique la cantidad (máximo / mínimo).',
        ], [
            'scope' => 'ámbito', 'subject' => 'sujeto', 'rule' => 'condición', 'target' => 'objetivo', 'quantity' => 'cantidad',
        ]);

        [$subjectType, $subjectRef] = explode(':', $data['subject'], 2);
        $hasTarget = in_array($data['rule'], [TicketRule::REQUIRES, TicketRule::EXCLUDES], true);
        [$targetType, $targetRef] = $hasTarget ? explode(':', $data['target'], 2) : [null, null];

        $rule = $event->ticketRules()->create([
            'scope' => $data['scope'],
            'subject_type' => $subjectType,
            'subject_ref' => $subjectRef,
            'rule' => $data['rule'],
            'target_type' => $targetType,
            'target_ref' => $targetRef,
            'quantity' => $data['rule'] === TicketRule::EXCLUDES ? null : ($data['quantity'] ?? 1),
            'position' => (int) $event->ticketRules()->max('position') + 1,
        ]);

        Log::create(['area' => 'Eventos', 'module' => 'Reglas', 'action' => 'Creó regla ' . $rule->id . ' en ' . $event->name, 'user_id' => \Auth::user()->id]);
        \Session::flash('success_alert', 'La regla se agregó.');
        return redirect()->route('events.rules', $event->id);
    }

    public function destroy($id, $ruleId)
    {
        $event = Event::findOrFail($id);
        $event->ticketRules()->where('id', $ruleId)->delete();

        Log::create(['area' => 'Eventos', 'module' => 'Reglas', 'action' => 'Eliminó regla ' . $ruleId . ' en ' . $event->name, 'user_id' => \Auth::user()->id]);
        \Session::flash('success_alert', 'La regla se eliminó.');
        return redirect()->route('events.rules', $event->id);
    }

    // Valores válidos de sujeto/objetivo: "ticket:id", "category:id", "kind:tipo".
    private function options(Event $event): array
    {
        return array_merge(
            $event->tickets()->pluck('id')->map(fn ($id) => TicketRule::TYPE_TICKET . ':' . $id)->all(),
            $event->ticketCategories()->pluck('id')->map(fn ($id) => TicketRule::TYPE_CATEGORY . ':' . $id)->all(),
            array_map(fn ($kind) => TicketRule::TYPE_KIND . ':' . $kind, EventTicket::KINDS)
        );
    }
}

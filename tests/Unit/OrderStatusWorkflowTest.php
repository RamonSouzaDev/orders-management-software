<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários do workflow de status.
 */
class OrderStatusWorkflowTest extends TestCase
{
    /**
     * Testa transição válida de draft para pending.
     */
    public function test_can_transition_from_draft_to_pending(): void
    {
        $status = OrderStatus::DRAFT;

        $this->assertTrue($status->canTransitionTo(OrderStatus::PENDING));
    }

    /**
     * Testa transição válida de pending para paid.
     */
    public function test_can_transition_from_pending_to_paid(): void
    {
        $status = OrderStatus::PENDING;

        $this->assertTrue($status->canTransitionTo(OrderStatus::PAID));
    }

    /**
     * Testa transição válida de pending para cancelled.
     */
    public function test_can_transition_from_pending_to_cancelled(): void
    {
        $status = OrderStatus::PENDING;

        $this->assertTrue($status->canTransitionTo(OrderStatus::CANCELLED));
    }

    /**
     * Testa que draft não pode ir direto para paid.
     */
    public function test_cannot_transition_from_draft_to_paid(): void
    {
        $status = OrderStatus::DRAFT;

        $this->assertFalse($status->canTransitionTo(OrderStatus::PAID));
    }

    /**
     * Testa que draft não pode ir para cancelled.
     */
    public function test_cannot_transition_from_draft_to_cancelled(): void
    {
        $status = OrderStatus::DRAFT;

        $this->assertFalse($status->canTransitionTo(OrderStatus::CANCELLED));
    }

    /**
     * Testa que paid é estado final (não pode transicionar).
     */
    public function test_paid_is_final_state(): void
    {
        $status = OrderStatus::PAID;

        $this->assertTrue($status->isFinal());
        $this->assertFalse($status->canTransitionTo(OrderStatus::DRAFT));
        $this->assertFalse($status->canTransitionTo(OrderStatus::PENDING));
        $this->assertFalse($status->canTransitionTo(OrderStatus::CANCELLED));
    }

    /**
     * Testa que cancelled é estado final (não pode transicionar).
     */
    public function test_cancelled_is_final_state(): void
    {
        $status = OrderStatus::CANCELLED;

        $this->assertTrue($status->isFinal());
        $this->assertFalse($status->canTransitionTo(OrderStatus::DRAFT));
        $this->assertFalse($status->canTransitionTo(OrderStatus::PENDING));
        $this->assertFalse($status->canTransitionTo(OrderStatus::PAID));
    }

    /**
     * Testa transições permitidas de cada status.
     */
    public function test_allowed_transitions_for_each_status(): void
    {
        // Draft -> só pending
        $this->assertEquals(
            [OrderStatus::PENDING],
            OrderStatus::DRAFT->allowedTransitions()
        );

        // Pending -> paid ou cancelled
        $this->assertEquals(
            [OrderStatus::PAID, OrderStatus::CANCELLED],
            OrderStatus::PENDING->allowedTransitions()
        );

        // Paid -> nenhum (final)
        $this->assertEquals(
            [],
            OrderStatus::PAID->allowedTransitions()
        );

        // Cancelled -> nenhum (final)
        $this->assertEquals(
            [],
            OrderStatus::CANCELLED->allowedTransitions()
        );
    }

    /**
     * Testa que todos os status têm labels definidos.
     */
    public function test_all_statuses_have_labels(): void
    {
        foreach (OrderStatus::cases() as $status) {
            $this->assertNotEmpty($status->label());
            $this->assertIsString($status->label());
        }
    }

    /**
     * Testa que todos os status têm cores definidas.
     */
    public function test_all_statuses_have_colors(): void
    {
        foreach (OrderStatus::cases() as $status) {
            $this->assertNotEmpty($status->color());
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $status->color());
        }
    }
}


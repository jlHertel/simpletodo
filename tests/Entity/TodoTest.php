<?php

namespace Entity;

use App\Entity\Todo;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class TodoTest extends TestCase {
    public function testItCopiesPropertiesCorrectly(): void {
        $todo1 = new Todo();
        $todo1->setId(5);
        $todo1->setDescription('Some description');
        $todo1->setTitle('Some title');
        $todo1->setStatus('pending');

        $todo2 = new Todo();
        $todo2->setId(20);
        $todo2->setDescription('Another description');
        $todo2->setTitle('Another title');
        $todo2->setStatus('completed');

        $todo1->updateFrom($todo2);

        assertEquals('Another description', $todo1->getDescription());
        assertEquals('Another title', $todo1->getTitle());
        assertEquals('completed', $todo1->getStatus());
        assertEquals(5, $todo1->getId());
    }
}
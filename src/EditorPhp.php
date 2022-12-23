<?php

namespace BumpCore\EditorPhp;

use BumpCore\EditorPhp\Block\Block;
use BumpCore\EditorPhp\Block\BlockCollection;
use BumpCore\EditorPhp\Contracts\Provider;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class EditorPhp implements Arrayable, Jsonable
{
    /**
     * @var array
     */
    protected array $providers;

    /**
     * @var string
     */
    protected string $version;

    /**
     * @var BlockCollection<int, Block>
     */
    public readonly BlockCollection $blocks;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->providers = [];
        $this->blocks = new BlockCollection();
    }

    /**
     * Registers new providers for the blocks.
     *
     * @param array $providers
     *
     * @return void
     */
    public function register(array $providers): void
    {
        foreach ($providers as $provider)
        {
            if (!in_array(Provider::class, class_implements($provider)))
            {
                throw new Exception($provider . ' must implement ' . Provider::class);
            }

            /** @var Provider */
            $provider =  new ($provider);

            $this->providers[strtolower($provider->type())] = $provider;
        }
    }

    /**
     * Parses the given output.
     *
     * @param string $output Json output of the Editor.js
     *
     * @return $this
     */
    public function load(string $output): self
    {
        $output = json_decode($output, true);

        if (!empty($blocks = $output['blocks']))
        {
            $this->blocks
                ->clear()
                ->push(...$this->parseBlocks($blocks));
        }

        if (!empty($version = $output['version']))
        {
            $this->version = $version;
        }

        return $this;
    }

    /**
     * Parses blocks from given array.
     *
     * @param array $blocks
     *
     * @return array
     */
    protected function parseBlocks(array $blocks): array
    {
        $parsed = [];

        foreach ($blocks as $block)
        {
			$blockType = strtolower($block['type']);

            if ($this->providerExists($blockType))
            {
                $parsed[] = new Block($this->providers[$blockType], $block['data']);
            }
        }

        return $parsed;
    }

    /**
     * Checks if block provider exists.
     *
     * @param string $type
     *
     * @return bool
     */
    protected function providerExists(string $type): bool
    {
        return key_exists($type, $this->providers);
    }

    /**
     * Converts EditorPhp into array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'time' => floor(microtime(true) * 1000),
            'blocks' => $this->blocks->toArray(),
            'version' => $this->version,
        ];
    }

    /**
     * Encodes EditorPhp into Editor.js readable format.
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Renders all blocks.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Renders all blocks.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->blocks
            ->map(fn (Block $block) => $block->render())
            ->implode('');
    }
}

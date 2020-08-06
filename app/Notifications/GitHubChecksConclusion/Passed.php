<?php

declare(strict_types=1);

namespace App\Notifications\GitHubChecksConclusion;

class Passed extends Kernel
{
    /**
     * @throws \Exception
     *
     * @return string
     */
    public function markdown()
    {
        return self::$header.<<<EOF

## Build Configuration

|Build Option      | Setting    |
| --               |   --       |
| Language         | $this->language  |
| Operating System | $this->os        |

<details>
<summary><strong>Build Configuration</strong></summary>

```json
$this->config
```

</details>

EOF;
    }
}

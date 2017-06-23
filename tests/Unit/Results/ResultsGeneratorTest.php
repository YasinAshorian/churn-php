<?php declare(strict_types = 1);

namespace Churn\Tests\Results;

use Churn\Tests\BaseTestCase;
use Churn\Results\ResultsGenerator;
use Churn\Results\ResultCollection;
use Churn\Services\CommandService;
use Churn\Assessors\GitCommitCount\GitCommitCountAssessor;
use Churn\Assessors\CyclomaticComplexity\CyclomaticComplexityAssessor;
use Mockery as m;

class ResultsGeneratorTest extends BaseTestCase
{
    /**
     * The object we're testing.
     * @var ResultsGenerator
     */
    protected $resultsGenerator;

    /**
     * The results generated by the ResultsGenerator
     * @var Results
     */
    protected $results;

    /** @test */
    public function it_can_be_created()
    {
        $this->assertInstanceOf(ResultsGenerator::class, $this->resultsGenerator);
    }

    /** @test */
    public function it_generates_a_ResultCollection_object()
    {
        $this->assertInstanceOf(ResultCollection::class, $this->results);
    }

    public function setup()
    {
        parent::setup();
        $commandService = m::mock(CommandService::class);
        $commandService->shouldReceive('execute')
            ->once()
            ->with("cd /foo/bar && git log --name-only --pretty=format: /foo/bar/Baz.php | sort | uniq -c | sort -nr")
            ->andReturn(['      5 /foo/bar/Baz.php']);

        $commitCountAssessor = new GitCommitCountAssessor($commandService);
        $cyclomaticComplexityAssessor = m::mock(CyclomaticComplexityAssessor::class);
        $cyclomaticComplexityAssessor->shouldReceive('assess')
            ->once()
            ->with('/foo/bar/Baz.php')
            ->andReturn(6);

        $this->resultsGenerator = new ResultsGenerator($commitCountAssessor, $cyclomaticComplexityAssessor);

        $this->results = $this->resultsGenerator->getResults([
            [
                "fullPath"    => "/foo/bar/Baz.php",
                "displayPath" => "src/Assessors/CyclomaticComplexity/CyclomaticComplexityAssessor.php",
            ]
        ]);
    }

}
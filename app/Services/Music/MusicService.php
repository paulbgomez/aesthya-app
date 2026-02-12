<?php

namespace App\Services\Music;

use App\Models\MusicTrack;

class MusicService
{
	public function __construct(
		private YoutubeService $youtubeService,
	) {}

	public function processTrackData(array $track): ?MusicTrack
	{
		$title = $track['title'] ?? 'Unknown';
		$artist = $track['artist'] ?? 'Unknown';

		$existingTrack = MusicTrack::where('title', $title)
			->where('artist', $artist)
			->first();

		if ($existingTrack) {
			return $existingTrack;
		}

		$youtubeData = $title !== 'Unknown'
			? $this->youtubeService->searchTrackByTitle($title, $artist)
			: null;

		$trackData = [
			'title' => $title,
			'artist' => $artist,
			'preview_url' => $youtubeData['youtube_url'] ?? null,
			'album_art' => $youtubeData['thumbnail_url'] ?? null,
			'metadata' => array_filter([
				'youtube_url' => $youtubeData['youtube_url'] ?? null,
				'video_id' => $youtubeData['video_id'] ?? null,
				'source' => $youtubeData ? 'youtube' : 'llm',
			]),
		];

		return MusicTrack::create($trackData);
	}
}

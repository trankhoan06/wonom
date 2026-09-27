const fs = require('fs');
const path = require('path');

const sampleRate = 44100;
const duration = 1.2;
const notes = [
    { name: 'piano-c4', frequency: 261.63 },
    { name: 'piano-d4', frequency: 293.66 },
    { name: 'piano-e4', frequency: 329.63 },
    { name: 'piano-f4', frequency: 349.23 },
    { name: 'piano-g4', frequency: 392.00 },
];

const outputDirectory = path.join(__dirname, 'asset', 'audio');
fs.mkdirSync(outputDirectory, { recursive: true });

function exponentialRamp(start, end, time, rampDuration) {
    if (time >= rampDuration) return end;
    return start * Math.pow(end / start, time / rampDuration);
}

function triangleWave(phase) {
    return (2 / Math.PI) * Math.asin(Math.sin(phase));
}

function createWaveFile(frequency) {
    const sampleCount = Math.ceil(sampleRate * duration);
    const dataSize = sampleCount * 2;
    const buffer = Buffer.alloc(44 + dataSize);

    buffer.write('RIFF', 0);
    buffer.writeUInt32LE(36 + dataSize, 4);
    buffer.write('WAVE', 8);
    buffer.write('fmt ', 12);
    buffer.writeUInt32LE(16, 16);
    buffer.writeUInt16LE(1, 20);
    buffer.writeUInt16LE(1, 22);
    buffer.writeUInt32LE(sampleRate, 24);
    buffer.writeUInt32LE(sampleRate * 2, 28);
    buffer.writeUInt16LE(2, 32);
    buffer.writeUInt16LE(16, 34);
    buffer.write('data', 36);
    buffer.writeUInt32LE(dataSize, 40);

    for (let index = 0; index < sampleCount; index += 1) {
        const time = index / sampleRate;
        const fundamentalGain = exponentialRamp(0.4, 0.001, time, 1.2);
        const fundamental = triangleWave(2 * Math.PI * frequency * time) * fundamentalGain;
        const harmonic = time < 0.8
            ? Math.sin(2 * Math.PI * frequency * 2 * time) * exponentialRamp(0.12, 0.001, time, 0.8)
            : 0;
        const sample = Math.max(-1, Math.min(1, fundamental + harmonic));

        buffer.writeInt16LE(Math.round(sample * 32767), 44 + (index * 2));
    }

    return buffer;
}

notes.forEach((note) => {
    fs.writeFileSync(
        path.join(outputDirectory, `${note.name}.wav`),
        createWaveFile(note.frequency)
    );
});

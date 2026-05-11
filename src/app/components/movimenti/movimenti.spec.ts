import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Movimenti } from './movimenti';

describe('Movimenti', () => {
  let component: Movimenti;
  let fixture: ComponentFixture<Movimenti>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Movimenti],
    }).compileComponents();

    fixture = TestBed.createComponent(Movimenti);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
